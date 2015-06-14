<?php
/**
 * Format messages with different options/parsers
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser;

use MyBB\Parser\Database\Repositories\BadWordRepositoryInterface;
use MyBB\Parser\Database\Repositories\SmileyRepositoryInterface;
use MyBB\Parser\Exceptions\ParserSearchWordMinimumException;
use MyBB\Parser\Parser\ParserInterface;

class MessageFormatter
{
	/**
	 * @var ParserInterface
	 */
	private $parser;
	/**
	 * @var \HTMLPurifier
	 */
	private $htmlPurifier;
	/**
	 * @var SmileyRepositoryInterface
	 */
	private $smileys;
	/**
	 * @var BadWordRepositoryInterface
	 */
	private $badWords;
	/**
	 * @var array
	 */
	private $highlight_cache = array();
	/**
	 * @var integer
	 */
	private $minSearchWord = 3;

	const ENABLE_SMILEYS   = 'enable_smilies';
	const ENABLE_MYCODE    = 'enable_mycode';
	const ALLOW_HTML       = 'allow_html';
	const FILTER_BAD_WORDS = 'filter_badwords';
	const FILTER_CDATA     = 'filter_cdata';
	const ME_USERNAME      = 'me_username';
	const HIGHLIGHT        = 'highlight';
	const NL2BR            = 'nl2br';

	/**
	 * @param ParserInterface            $parser
	 * @param \HTMLPurifier              $htmlPurifier
	 * @param SmileyRepositoryInterface  $smileys
	 * @param BadWordRepositoryInterface $badWords
	 */
	public function __construct(
		ParserInterface $parser,
		\HTMLPurifier $htmlPurifier,
		SmileyRepositoryInterface $smileys,
		BadWordRepositoryInterface $badWords
	) {
		$this->parser = $parser;
		$this->htmlPurifier = $htmlPurifier;
		$this->smileys = $smileys;
		$this->badWords = $badWords;
	}

	/**
	 * Parse the message with the given options
	 *
	 * @param string $message
	 * @param array  $options
	 *
	 * @return string
	 */
	public function parse($message, array $options = [])
	{
		$options = array_merge(
			[
				static::ENABLE_SMILEYS   => true,
				static::ENABLE_MYCODE    => true,
				static::ALLOW_HTML       => false,
				static::FILTER_BAD_WORDS => true,
				static::FILTER_CDATA     => false,
				static::ME_USERNAME      => "",
				static::HIGHLIGHT        => "",
				static::NL2BR            => true,
			],
			$options
		);

		if ($options[static::FILTER_BAD_WORDS]) {
			$message = $this->filterBadwords($message);
		}

		if ($options[static::FILTER_CDATA]) {
			$message = $this->filterCdata($message);
		}

		if (!$options[static::ALLOW_HTML]) {
			$message = $this->filterHtml($message);
		} else {/*      ------- OLD CODE ------ used in 1.x to strip out any dangerous tags. HTMLPurifier completely removes them so this is disabled atm.
                 Probably better to escape them instead of stripping them so left this code here for now
            while(preg_match("#<s(cript|tyle)(.*)>(.*)</s(cript|tyle)(.*)>#is", $message))
            {
                $message = preg_replace("#<s(cript|tyle)(.*)>(.*)</s(cript|tyle)(.*)>#is", "&lt;s$1$2&gt;$3&lt;/s$4$5&gt;", $message);
            }
            $find = array('<?php', '<!--', '-->', '?>', "<br />\n", "<br>\n");
            $replace = array('&lt;?php', '&lt;!--', '--&gt;', '?&gt;', "\n", "\n");
            $message = str_replace($find, $replace, $message);

             $message = preg_replace_callback("#<((m[^a])|(b[^diloru>])|(s[^aemptu>]))(\s*[^>]*)>#si", create_function(
                '$matches',
                'return e($matches[0]);'
            ), $message);//*/
		}

		// Most cases are handled by the HTML Purifier. But as always: better escape too often than otherwise
		$message = $this->fixJavascript($message);

		if (!empty($options[static::ME_USERNAME])) {
			$slapUsername = $options[static::ME_USERNAME];
			$message = preg_replace(
				'#(>|^|\r|\n)/me ([^\r\n<]*)#i',
				"\\1<span style=\"color: red;\">* {$slapUsername} \\2</span>",
				$message
			);
			$slap = trans('parser::parser.slap');
			$withTrout = trans('parser::parser.withTrout');
			$message = preg_replace(
				'#(>|^|\r|\n)/slap ([^\r\n<]*)#i',
				"\\1<span style=\"color: red;\">* {$slapUsername} {$slap} \\2 {$withTrout}</span>",
				$message
			);
		}

		if ($options[static::ENABLE_SMILEYS]) {
			$message = $this->replaceSmilies($message);
		}

		if ($options[static::ENABLE_MYCODE]) {
			$message = $this->parser->parse(
				$message,
				$options[static::ALLOW_HTML]
			);
		}

		if (!empty($options[static::HIGHLIGHT])) {
			$message = $this->highlight($message, $options[static::HIGHLIGHT]);
		}

		if ($options[static::NL2BR]) {
			$message = nl2br($message);
			// Fix up new lines and block level elements
			$message = preg_replace(
				"#(</?(?:html|head|body|div|p|form|table|thead|tbody|tfoot|tr|td|th|ul|ol|li|div|p|blockquote|cite|hr)[^>]*>)\s*<br />#i",
				"$1",
				$message
			);
			$message = preg_replace(
				"#(&nbsp;)+(</?(?:html|head|body|div|p|form|table|thead|tbody|tfoot|tr|td|th|ul|ol|li|div|p|blockquote|cite|hr)[^>]*>)#i",
				"$2",
				$message
			);
		}

		// Allow iframes to media sites. Doing it here to make sure they are set in any case
		$this->htmlPurifier->config->set('HTML.SafeIframe', true);
		$this->htmlPurifier->config->set(
			'URI.SafeIframeRegexp',
			'#(dailymotion.com|facebook.com|liveleak.com|metacafe.com|myspace.com|vimeo.com|yahoo.com|youtube.com)#'
		);
		// Used by veoh
		$this->htmlPurifier->config->set('HTML.SafeObject', true);
		$this->htmlPurifier->config->set('Output.FlashCompat', true);

		return $this->htmlPurifier->purify($message);
	}

	/**
	 * parsePlain *should* strip any codes and should return a plain text
	 * message However due some historic reasons not all codes are properly
	 * removed
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public function parsePlain($message)
	{
		$message = $this->filterHtml($message);

		return $this->htmlPurifier->purify($this->parser->parsePlain($message));
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function replaceSmilies($message)
	{
		$smilies = $this->smileys->getParsableSmilies();

		if (empty($smilies)) {
			return $message;
		}

		// TODO: this is mycode but it's not the parser! Should be changed as we plan to also support markdown
		// First we take out any of the tags we don't want parsed between (url= etc)
		preg_match_all(
			"#\[(url(=[^\]]*)?\]|quote=([^\]]*)?\])|(http|ftp)(s|)://[^\s]*#i",
			$message,
			$bad_matches,
			PREG_PATTERN_ORDER
		);
		if (count($bad_matches[0]) > 0) {
			$message = preg_replace(
				"#\[(url(=[^\]]*)?\]|quote=([^\]]*)?\])|(http|ftp)(s|)://[^\s]*#si",
				"<mybb-bad-sm>",
				$message
			);
		}

		$message = strtr($message, $smilies);

		// If we matched any tags previously, swap them back in
		if (count($bad_matches[0]) > 0) {
			$message = explode("<mybb-bad-sm>", $message);
			$i = 0;
			foreach ($bad_matches[0] as $match) {
				$message[$i] .= $match;
				$i++;
			}
			$message = implode("", $message);
		}

		return $message;
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function filterHtml($message)
	{
		$message = preg_replace(
			"#&(?!\#[0-9]+;)#si",
			"&amp;",
			$message
		); // fix & but allow unicode
		$message = str_replace("<", "&lt;", $message);
		$message = str_replace(">", "&gt;", $message);

		return $message;
	}

	/**
	 * @param string $message
	 * @param bool   $stripTags
	 *
	 * @return string
	 */
	public function filterBadwords($message, $stripTags = false)
	{
		$badwords = $this->badWords->getAllAsArray();
		if (!empty($badwords) && is_array(($badwords))) {
			reset($badwords);
			foreach ($badwords as $find => $replace) {
				if (empty($replace)) {
					$replace = "*****";
				}
				// Take into account the position offset for our last replacement.
				$index = substr_count($find, '*') + 2;
				$find = str_replace(
					'\*',
					'([a-zA-Z0-9_]{1})',
					preg_quote($find, "#")
				);
				// Ensure we run the replacement enough times but not recursively (i.e. not while(preg_match..))
				$count = preg_match_all(
					"#(^|\W)" . $find . "(\W|$)#i",
					$message,
					$matches
				);
				for ($i = 0; $i < $count; ++$i) {
					$message = preg_replace(
						"#(^|\W)" . $find . "(\W|$)#i",
						"\\1" . $replace . '\\' . $index,
						$message
					);
				}
			}
		}
		if ($stripTags) {
			$message = strip_tags($message);
		}

		return $message;
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function filterCdata($message)
	{
		return str_replace(']]>', ']]]]><![CDATA[>', $message);
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function fixJavascript($message)
	{
		$js_array = array(
			"#(&\#(0*)106;?|&\#(0*)74;?|&\#x(0*)4a;?|&\#x(0*)6a;?|j)((&\#(0*)97;?|&\#(0*)65;?|a)(&\#(0*)118;?|&\#(0*)86;?|v)(&\#(0*)97;?|&\#(0*)65;?|a)(\s)?(&\#(0*)115;?|&\#(0*)83;?|s)(&\#(0*)99;?|&\#(0*)67;?|c)(&\#(0*)114;?|&\#(0*)82;?|r)(&\#(0*)105;?|&\#(0*)73;?|i)(&\#112;?|&\#(0*)80;?|p)(&\#(0*)116;?|&\#(0*)84;?|t)(&\#(0*)58;?|\:))#i",
			"#(o)(nmouseover\s?=)#i",
			"#(o)(nmouseout\s?=)#i",
			"#(o)(nmousedown\s?=)#i",
			"#(o)(nmousemove\s?=)#i",
			"#(o)(nmouseup\s?=)#i",
			"#(o)(nclick\s?=)#i",
			"#(o)(ndblclick\s?=)#i",
			"#(o)(nload\s?=)#i",
			"#(o)(nsubmit\s?=)#i",
			"#(o)(nblur\s?=)#i",
			"#(o)(nchange\s?=)#i",
			"#(o)(nfocus\s?=)#i",
			"#(o)(nselect\s?=)#i",
			"#(o)(nunload\s?=)#i",
			"#(o)(nkeypress\s?=)#i",
			"#(o)(nerror\s?=)#i",
			"#(o)(nreset\s?=)#i",
			"#(o)(nabort\s?=)#i"
		);

		$message = preg_replace($js_array, "$1<strong></strong>$2$6", $message);

		return $message;
	}

	/**
	 * @param string $message
	 * @param string $highlight
	 *
	 * @return string
	 */
	private function highlight($message, $highlight)
	{
		if (empty($this->highlight_cache)) {
			$this->highlight_cache = $this->buildHighlightArray($highlight);
		}
		if (is_array(
				$this->highlight_cache
			) && !empty($this->highlight_cache)
		) {
			$message = preg_replace(
				array_keys($this->highlight_cache),
				$this->highlight_cache,
				$message
			);
		}

		return $message;
	}

	/**
	 * @param string|array $terms
	 *
	 * @return array|bool
	 */
	private function buildHighlightArray($terms)
	{
		if (is_array($terms)) {
			$terms = implode(' ', $terms);
		}
		// Strip out any characters that shouldn't be included
		$bad_characters = array(
			"(",
			")",
			"+",
			"-",
			"~"
		);
		$terms = str_replace($bad_characters, '', $terms);
		// Check if this is a "series of words" - should be treated as an EXACT match
		if (strpos($terms, "\"") !== false) {
			$inquote = false;
			$terms = explode("\"", $terms);
			$words = array();
			foreach ($terms as $phrase) {
				$phrase = e($phrase);
				if ($phrase != "") {
					if ($inquote) {
						$words[] = trim($phrase);
					} else {
						$split_words = preg_split("#\s{1,}#", $phrase, -1);
						if (!is_array($split_words)) {
							continue;
						}
						foreach ($split_words as $word) {
							if (!$word || strlen(
									$word
								) < $this->minSearchWord
							) {
								continue;
							}
							$words[] = trim($word);
						}
					}
				}
				$inquote = !$inquote;
			}
		} // Otherwise just a simple search query with no phrases
		else {
			$terms = e($terms);
			$split_words = preg_split("#\s{1,}#", $terms, -1);
			if (is_array($split_words)) {
				foreach ($split_words as $word) {
					if (!$word || strlen($word) < $this->minSearchWord) {
						continue;
					}
					$words[] = trim($word);
				}
			}
		}
		if (!isset($words) || !is_array($words)) {
			return false;
		}

		$highlight_cache = array();

		// Sort the word array by length. Largest terms go first and work their way down to the smallest term.
		// This resolves problems like "test tes" where "tes" will be highlighted first,
		// then "test" can't be highlighted because of the changed html
		usort(
			$words,
			create_function('$a,$b', 'return strlen($b) - strlen($a);')
		);
		// Loop through our words to build the PREG compatible strings
		foreach ($words as $word) {
			$word = trim($word);
			$word = strtolower($word);
			// Special boolean operators should be stripped
			if ($word == "" || $word == "or" || $word == "not" || $word == "and") {
				continue;
			}
			// Now make PREG compatible
			$find = "#(?!<.*?)(" . preg_quote($word, "#") . ")(?![^<>]*?>)#ui";
			$replacement = "<span class=\"highlight\" style=\"padding-left: 0px; padding-right: 0px;\">$1</span>";
			$highlight_cache[$find] = $replacement;
		}

		return $highlight_cache;
	}

	/**
	 * Set the minimum length needed to highlight a word.
	 *
	 * @param int $min The minimum length.
	 *
	 * @throws ParserSearchWordMinimumException Thrown if $min is less than 1.
	 */
	public function setMinSearchWord($min = 1)
	{
		$min = (int) $min;

		if ($min < 1) {
			throw new ParserSearchWordMinimumException;
		}

		$this->minSearchWord = $min;
	}
}
