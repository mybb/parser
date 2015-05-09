<?php
/**
 * MyCode/BBCode parser
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser;

use MyBB\Parser\Parser\CustomCodes\CustomCodeRepositoryInterface;

class MyCode implements ParserInterface
{
	/**
	 * @var integer
	 */
	private $mycode_cache = 0;
	/**
	 * @var array
	 */
	private $list_elements;
	/**
	 * @var integer
	 */
	private $list_count;
	/**
	 * @var boolean
	 */
	private $allowbasicmycode = true;
	/**
	 * @var boolean
	 */
	private $allowsymbolmycode = true;
	/**
	 * @var boolean
	 */
	private $allowlinkmycode = true;
	/**
	 * @var boolean
	 */
	private $allowemailmycode = true;
	/**
	 * @var boolean
	 */
	private $allowcolormycode = true;
	/**
	 * @var boolean
	 */
	private $allowsizemycode = true;
	/**
	 * @var boolean
	 */
	private $allowfontmycode = true;
	/**
	 * @var boolean
	 */
	private $allowalignmycode = true;
	/**
	 * @var boolean
	 */
	private $allowlistmycode = true;
	/**
	 * @var boolean
	 */
	private $allowimgcode = true;
	/**
	 * @var boolean
	 */
	private $allowvideocode = true;
	/**
	 * @var boolean
	 */
	private $shorten_urls = false;
	/**
	 * @var boolean
	 */
	private $nofollow_on = false;
	/**
	 * @var boolean
	 */
	private $allowHtml = false;
	/**
	 * @var string|callable
	 */
	private $dateFormat = 'd.m.Y H:i:s';
	/**
	 * @var string|callable
	 */
	private $postURL = '';
	/**
	 * @var CustomCodeRepositoryInterface
	 */
	private $customCodeRepository;

	/**
	 * @param CustomCodeRepositoryInterface $customCodeRepository
	 */
	public function __construct(CustomCodeRepositoryInterface $customCodeRepository)
	{
		$this->customCodeRepository = $customCodeRepository;

		$this->allowbasicmycode = config('parser.allowbasicmycode');
		$this->allowsymbolmycode = config('parser.allowsymbolmycode');
		$this->allowlinkmycode = config('parser.allowlinkmycode');
		$this->allowemailmycode = config('parser.allowemailmycode');
		$this->allowcolormycode = config('parser.allowcolormycode');
		$this->allowsizemycode = config('parser.allowsizemycode');
		$this->allowfontmycode = config('parser.allowfontmycode');
		$this->allowalignmycode = config('parser.allowalignmycode');
		$this->allowlistmycode = config('parser.allowlistmycode');
		$this->allowimgcode = config('parser.allowimgcode');
		$this->allowvideocode = config('parser.allowvideocode');
		$this->shorten_urls = config('parser.shorten_urls');
		$this->nofollow_on = config('parser.nofollow_on');
		$this->dateFormat = config('parser.dateFormat');
		$this->postURL = config('parser.postURL');
	}

	/**
	 * Parse a message into a HTML string ready for display.
	 *
	 * @param string $message   The message to parse.
	 * @param bool   $allowHtml Whether to allow HTML in the message.
	 *
	 * @return string
	 */
	public function parse($message, $allowHtml)
	{
		$this->allowHtml = $allowHtml;

		// If MyCode needs to be replaced, first filter out [code] and [php] tags.
		preg_match_all("#\[(code|php)\](.*?)\[/\\1\](\r\n?|\n?)#si", $message, $code_matches, PREG_SET_ORDER);
		$message = preg_replace("#\[(code|php)\](.*?)\[/\\1\](\r\n?|\n?)#si", "<mybb-code>\n", $message);

		$message = $this->parseMyCode($message);

		// Now that we're done, if we split up any code tags, parse them and glue it all back together
		if (count($code_matches) > 0) {
			foreach ($code_matches as $text) {
				// Fix up HTML inside the code tags so it is clean
				if ($this->allowHtml) {
					$text[2] = $this->fixHtml($text[2]);
				}
				$code = "";
				if (strtolower($text[1]) == "code") {
					$code = $this->parseCode($text[2]);
				} elseif (strtolower($text[1]) == "php") {
					$code = $this->parsePhp($text[2]);
				}
				$message = preg_replace("#\<mybb-code>\n?#", $code, $message, 1);
			}
		}

		return $message;
	}

	/**
	 * @param string $message
	 * @param array  $options
	 *
	 * @return string
	 */
	public function parsePlain($message, $options = array())
	{
		// Parse quotes first
		$message = $this->parseQuotes($message, true);
		$message = preg_replace_callback(
			"#\[php\](.*?)\[/php\](\r\n?|\n?)#is",
			array($this, 'parsePhpCallback'),
			$message
		);
		$message = preg_replace_callback(
			"#\[code\](.*?)\[/code\](\r\n?|\n?)#is",
			array($this, 'parseCodeCallback'),
			$message
		);
		$find = array(
			"#\[(b|u|i|s|url|email|color|img)\](.*?)\[/\\1\]#is",
			"#\[img=([0-9]{1,3})x([0-9]{1,3})\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
			"#\[url=([a-z]+?://)([^\r\n\"<]+?)\](.+?)\[/url\]#si",
			"#\[url=([^\r\n\"<&\(\)]+?)\](.+?)\[/url\]#si",
		);
		$replace = array(
			"$2",
			"$4",
			"$3 ($1$2)",
			"$2 ($1)",
		);
		$message = preg_replace($find, $replace, $message);

		// Reset list cache
		$this->list_elements = array();
		$this->list_count = 0;
		// Find all lists
		$message = preg_replace_callback(
			"#(\[list(=(a|A|i|I|1))?\]|\[/list\])#si",
			array($this, 'prepareList'),
			$message
		);
		// Replace all lists
		for ($i = $this->list_count; $i > 0; $i--) {
			// Ignores missing end tags
			$message = preg_replace_callback(
				"#\s?\[list(=(a|A|i|I|1))?&{$i}\](.*?)(\[/list&{$i}\]|$)(\r\n?|\n?)#si",
				array(
					$this,
					'parseListCallback'
				),
				$message,
				1
			);
		}

		return $message;
	}

	/**
	 * @param string|callable $format
	 */
	public function setDateFormatting($format)
	{
		$this->dateFormat = $format;
	}

	/**
	 * @param string|callable $url
	 */
	public function setPostURL($url)
	{
		$this->postURL = $url;
	}

	/**
	 * @param bool $allow
	 */
	public function allowBasicCode($allow = true)
	{
		$this->allowbasicmycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowSymbolCode($allow = true)
	{
		$this->allowsymbolmycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowLinkCode($allow = true)
	{
		$this->allowlinkmycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowEmailCode($allow = true)
	{
		$this->allowemailmycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowColorCode($allow = true)
	{
		$this->allowcolormycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowSizeCode($allow = true)
	{
		$this->allowsizemycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowFontCode($allow = true)
	{
		$this->allowfontmycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowAlignCode($allow = true)
	{
		$this->allowalignmycode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowImgCode($allow = true)
	{
		$this->allowimgcode = $allow;
	}

	/**
	 * @param bool $allow
	 */
	public function allowVideoCode($allow = true)
	{
		$this->allowvideocode = $allow;
	}

	/**
	 * @param bool $short
	 */
	public function shortenURLs($short = true)
	{
		$this->shorten_urls = $short;
	}

	/**
	 * @param bool $on
	 */
	public function useNoFollow($on = true)
	{
		$this->nofollow_on = $on;
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function parseMyCode($message)
	{
		// Cache the MyCode globally if needed.
		if ($this->mycode_cache == 0) {
			$this->cacheMyCode();
		}

		// Parse quotes first
		$message = $this->parseQuotes($message);
		$message = $this->autoUrl($message);
		$message = str_replace('$', '&#36;', $message);
		// Replace the rest
		if ($this->mycode_cache['standard_count'] > 0) {
			$message = preg_replace(
				$this->mycode_cache['standard']['find'],
				$this->mycode_cache['standard']['replacement'],
				$message
			);
		}
		if ($this->mycode_cache['callback_count'] > 0) {
			foreach ($this->mycode_cache['callback'] as $replace) {
				$message = preg_replace_callback($replace['find'], $replace['replacement'], $message);
			}
		}
		// Replace the nestable mycode's
		if ($this->mycode_cache['nestable_count'] > 0) {
			foreach ($this->mycode_cache['nestable'] as $mycode) {
				while (preg_match($mycode['find'], $message)) {
					$message = preg_replace($mycode['find'], $mycode['replacement'], $message);
				}
			}
		}
		// Reset list cache
		if ($this->allowlistmycode) {
			$this->list_elements = array();
			$this->list_count = 0;
			// Find all lists
			$message = preg_replace_callback(
				"#(\[list(=(a|A|i|I|1))?\]|\[/list\])#si",
				array($this, 'prepareList'),
				$message
			);
			// Replace all lists
			for ($i = $this->list_count; $i > 0; $i--) {
				// Ignores missing end tags
				$message = preg_replace_callback(
					"#\s?\[list(=(a|A|i|I|1))?&{$i}\](.*?)(\[/list&{$i}\]|$)(\r\n?|\n?)#si",
					array(
						$this,
						'parseListCallback'
					),
					$message,
					1
				);
			}
		}
		// Convert images when allowed.
		if ($this->allowimgcode) {
			$message = preg_replace_callback("#\[img\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is", array(
				$this,
				'parseImageCallback'
			), $message);
			$message = preg_replace_callback(
				"#\[img=([0-9]{1,3})x([0-9]{1,3})\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
				array(
					$this,
					'parseImageCallback2'
				),
				$message
			);
			$message = preg_replace_callback(
				"#\[img align=([a-z]+)\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
				array(
					$this,
					'parseImageCallback3'
				),
				$message
			);
			$message = preg_replace_callback(
				"#\[img=([0-9]{1,3})x([0-9]{1,3}) align=([a-z]+)\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
				array(
					$this,
					'parseImageCallback4'
				),
				$message
			);
		} else {
			$message = preg_replace_callback("#\[img\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is", array(
				$this,
				'parseImageDisabledCallback'
			), $message);
			$message = preg_replace_callback(
				"#\[img=([0-9]{1,3})x([0-9]{1,3})\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
				array(
					$this,
					'parseImageDisabledCallback2'
				),
				$message
			);
			$message = preg_replace_callback(
				"#\[img align=([a-z]+)\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
				array(
					$this,
					'parseImageDisabledCallback3'
				),
				$message
			);
			$message = preg_replace_callback(
				"#\[img=([0-9]{1,3})x([0-9]{1,3}) align=([a-z]+)\](\r\n?|\n?)(https?://([^<>\"']+?))\[/img\]#is",
				array(
					$this,
					'parseImageDisabledCallback4'
				),
				$message
			);
		}
		// Convert videos when allow.
		if ($this->allowvideocode) {
			$message = preg_replace_callback(
				"#\[video=(.*?)\](.*?)\[/video\]#i",
				array($this, 'parseVideoCallback'),
				$message
			);
		} else {
			$message = preg_replace_callback("#\[video=(.*?)\](.*?)\[/video\]#i", array(
				$this,
				'parseVideoDisabledCallback'
			), $message);
		}

		return $message;
	}

	/**
	 * Adds all codes to our internal cache
	 */
	private function cacheMyCode()
	{
		$this->mycode_cache = array();
		$standard_mycode = $callback_mycode = $nestable_mycode = array();
		$standard_count = $callback_count = $nestable_count = 0;
		if ($this->allowbasicmycode) {
			$standard_mycode['b']['regex'] = "#\[b\](.*?)\[/b\]#si";
			$standard_mycode['b']['replacement'] = "<span style=\"font-weight: bold;\">$1</span>";
			$standard_mycode['u']['regex'] = "#\[u\](.*?)\[/u\]#si";
			$standard_mycode['u']['replacement'] = "<span style=\"text-decoration: underline;\">$1</span>";
			$standard_mycode['i']['regex'] = "#\[i\](.*?)\[/i\]#si";
			$standard_mycode['i']['replacement'] = "<span style=\"font-style: italic;\">$1</span>";
			$standard_mycode['s']['regex'] = "#\[s\](.*?)\[/s\]#si";
			$standard_mycode['s']['replacement'] = "<del>$1</del>";
			$standard_mycode['hr']['regex'] = "#\[hr\]#si";
			$standard_mycode['hr']['replacement'] = "<hr />";
			++$standard_count;
		}
		if ($this->allowsymbolmycode) {
			$standard_mycode['copy']['regex'] = "#\(c\)#i";
			$standard_mycode['copy']['replacement'] = "&copy;";
			$standard_mycode['tm']['regex'] = "#\(tm\)#i";
			$standard_mycode['tm']['replacement'] = "&trade;";
			$standard_mycode['reg']['regex'] = "#\(r\)#i";
			$standard_mycode['reg']['replacement'] = "&reg;";
			++$standard_count;
		}
		if ($this->allowlinkmycode) {
			$callback_mycode['url_simple']['regex'] = "#\[url\]([a-z]+?://)([^\r\n\"<]+?)\[/url\]#si";
			$callback_mycode['url_simple']['replacement'] = array($this, 'parseUrlCallback1');
			$callback_mycode['url_simple2']['regex'] = "#\[url\]([^\r\n\"<]+?)\[/url\]#i";
			$callback_mycode['url_simple2']['replacement'] = array($this, 'parseUrlCallback2');
			$callback_mycode['url_complex']['regex'] = "#\[url=([a-z]+?://)([^\r\n\"<]+?)\](.+?)\[/url\]#si";
			$callback_mycode['url_complex']['replacement'] = array($this, 'parseUrlCallback1');
			$callback_mycode['url_complex2']['regex'] = "#\[url=([^\r\n\"<&\(\)]+?)\](.+?)\[/url\]#si";
			$callback_mycode['url_complex2']['replacement'] = array($this, 'parseUrlCallback2');
			++$callback_count;
		}
		if ($this->allowemailmycode) {
			$callback_mycode['email_simple']['regex'] = "#\[email\](.*?)\[/email\]#i";
			$callback_mycode['email_simple']['replacement'] = array($this, 'parseEmailCallback');
			$callback_mycode['email_complex']['regex'] = "#\[email=(.*?)\](.*?)\[/email\]#i";
			$callback_mycode['email_complex']['replacement'] = array($this, 'parseEmailCallback');
			++$callback_count;
		}
		if ($this->allowcolormycode) {
			$nestable_mycode['color']['regex'] =
				"#\[color=([a-zA-Z]*|\#?[\da-fA-F]{3}|\#?[\da-fA-F]{6})](.*?)\[/color\]#si";
			$nestable_mycode['color']['replacement'] = "<span style=\"color: $1;\">$2</span>";
			++$nestable_count;
		}
		if ($this->allowsizemycode) {
			$nestable_mycode['size']['regex'] =
				"#\[size=(xx-small|x-small|small|medium|large|x-large|xx-large)\](.*?)\[/size\]#si";
			$nestable_mycode['size']['replacement'] = "<span style=\"font-size: $1;\">$2</span>";
			$callback_mycode['size_int']['regex'] = "#\[size=([0-9\+\-]+?)\](.*?)\[/size\]#si";
			$callback_mycode['size_int']['replacement'] = array($this, 'handleSizeCallback');
			++$nestable_count;
			++$callback_count;
		}
		if ($this->allowfontmycode) {
			$nestable_mycode['font']['regex'] = "#\[font=([a-z0-9 ,\-_'\"]+)\](.*?)\[/font\]#si";
			$nestable_mycode['font']['replacement'] = "<span style=\"font-family: $1;\">$2</span>";
			++$nestable_count;
		}
		if ($this->allowalignmycode) {
			$nestable_mycode['align']['regex'] = "#\[align=(left|center|right|justify)\](.*?)\[/align\]#si";
			$nestable_mycode['align']['replacement'] = "<div style=\"text-align: $1;\">$2</div>";
			++$nestable_count;
		}

		$custom_mycode = $this->customCodeRepository->getParsableCodes();
		// If there is custom MyCode, load it.
		if (is_array($custom_mycode)) {
			foreach ($custom_mycode as $key => $mycode) {
				$mycode['regex'] = str_replace("\x0", "", $mycode['regex']);
				$custom_mycode[$key]['regex'] = "#" . $mycode['regex'] . "#si";
				++$standard_count;
			}
			$mycode = array_merge($standard_mycode, $custom_mycode);
		} else {
			$mycode = $standard_mycode;
		}
		// Assign the MyCode to the cache.
		foreach ($mycode as $code) {
			$this->mycode_cache['standard']['find'][] = $code['regex'];
			$this->mycode_cache['standard']['replacement'][] = $code['replacement'];
		}
		// Assign the nestable MyCode to the cache.
		foreach ($nestable_mycode as $code) {
			$this->mycode_cache['nestable'][] = array('find' => $code['regex'], 'replacement' => $code['replacement']);
		}
		// Assign the nestable MyCode to the cache.
		foreach ($callback_mycode as $code) {
			$this->mycode_cache['callback'][] = array('find' => $code['regex'], 'replacement' => $code['replacement']);
		}
		$this->mycode_cache['standard_count'] = $standard_count;
		$this->mycode_cache['callback_count'] = $callback_count;
		$this->mycode_cache['nestable_count'] = $nestable_count;
	}

	/**
	 * @param string $message
	 * @param bool   $text_only
	 *
	 * @return string
	 */
	private function parseQuotes($message, $text_only = false)
	{
		// Assign pattern and replace values.
		$pattern = "#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si";
		$pattern_callback = "#\[quote=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#si";
		$quote = trans('parser::parser.quote');
		if ($text_only == false) {
			$replace = "<blockquote><cite>$quote</cite>$1</blockquote>\n";
			$replace_callback = array($this, 'parsePostQuotesCallback1');
		} else {
			$replace = "\n{$quote}\n--\n$1\n--\n";
			$replace_callback = array($this, 'parsePostQuotesCallback2');
		}
		do {
			// preg_replace has erased the message? Restore it...
			$previous_message = $message;
			$message = preg_replace($pattern, $replace, $message, -1, $count);
			$message = preg_replace_callback($pattern_callback, $replace_callback, $message, -1, $count_callback);
			if (!$message) {
				$message = $previous_message;
				break;
			}
		} while ($count || $count_callback);
		if ($text_only == false) {
			$find = array(
				"#(\r\n*|\n*)<\/cite>(\r\n*|\n*)#",
				"#(\r\n*|\n*)<\/blockquote>#"
			);
			$replace = array(
				"</cite><br />",
				"</blockquote>"
			);
			$message = preg_replace($find, $replace, $message);
		}

		return $message;
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function autoUrl($message)
	{
		$message = " " . $message;
		// Links should end with slashes, numbers, characters and braces but not with dots, commas or question marks
		$message = preg_replace_callback(
			"#([\>\s\(\)])(http|https|ftp|news|irc|ircs|irc6){1}://([^\/\"\s\<\[\.]+\.([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/([^\"\s<\[]|\[\])*)?([\w\/\)]))#iu",
			array(
				$this,
				'autoUrlCallback'
			),
			$message
		);
		$message = preg_replace_callback(
			"#([\>\s\(\)])(www|ftp)\.(([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/([^\"\s<\[]|\[\])*)?([\w\/\)]))#iu",
			array(
				$this,
				'autoUrlCallback'
			),
			$message
		);
		$message = substr($message, 1);

		return $message;
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function fixHtml($message)
	{
		$message = preg_replace("#&(?!\#[0-9]+;)#si", "&amp;", $message); // fix & but allow unicode
		$message = str_replace("<", "&lt;", $message);
		$message = str_replace(">", "&gt;", $message);

		return $message;
	}

	/**
	 * @param string $code
	 * @param bool   $text_only
	 *
	 * @return string
	 */
	private function parseCode($code, $text_only = false)
	{
		$lcode = trans('parser::parser.code');
		if ($text_only == true) {
			return "\n{$lcode}\n--\n{$code}\n--\n";
		}
		// Clean the string before parsing.
		$code = preg_replace('#^(\t*)(\n|\r|\0|\x0B| )*#', '\\1', $code);
		$code = rtrim($code);
		$original = preg_replace('#^\t*#', '', $code);
		if (empty($original)) {
			return '';
		}
		$code = str_replace('$', '&#36;', $code);
		$code = preg_replace('#\$([0-9])#', '\\\$\\1', $code);
		$code = str_replace('\\', '&#92;', $code);
		$code = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $code);
		$code = str_replace("  ", '&nbsp;&nbsp;', $code);

		return "<div class=\"codeblock\">\n<div class=\"title\">" .
		$lcode . "\n</div><div class=\"body\" dir=\"ltr\"><code>" .
		$code . "</code></div></div>\n";
	}

	/**
	 * @param string $str
	 * @param bool   $bare_return
	 * @param bool   $text_only
	 *
	 * @return string
	 */
	private function parsePhp($str, $bare_return = false, $text_only = false)
	{
		$php_code = trans('parser::parser.php_code');
		if ($text_only == true) {
			return "\n{$php_code}\n--\n$str\n--\n";
		}
		// Clean the string before parsing except tab spaces.
		$str = preg_replace('#^(\t*)(\n|\r|\0|\x0B| )*#', '\\1', $str);
		$str = rtrim($str);
		$original = preg_replace('#^\t*#', '', $str);
		if (empty($original)) {
			return '';
		}
		$str = str_replace('&amp;', '&', $str);
		$str = str_replace('&lt;', '<', $str);
		$str = str_replace('&gt;', '>', $str);
		// See if open and close tags are provided.
		$added_open_tag = false;
		if (!preg_match("#^\s*<\?#si", $str)) {
			$added_open_tag = true;
			$str = "<?php \n" . $str;
		}
		$added_end_tag = false;
		if (!preg_match("#\?>\s*$#si", $str)) {
			$added_end_tag = true;
			$str = $str . " \n?>";
		}
		$code = @highlight_string($str, true);
		// Do the actual replacing.
		$code = preg_replace('#<code>\s*<span style="color: \#000000">\s*#i', "<code>", $code);
		$code = preg_replace("#</span>\s*</code>#", "</code>", $code);
		$code = preg_replace("#</span>(\r\n?|\n?)</code>#", "</span></code>", $code);
		$code = str_replace("\\", '&#092;', $code);
		$code = str_replace('$', '&#36;', $code);
		$code = preg_replace("#&amp;\#([0-9]+);#si", "&#$1;", $code);
		if ($added_open_tag) {
			$code = preg_replace(
				"#<code><span style=\"color: \#([A-Z0-9]{6})\">&lt;\?php( |&nbsp;)(<br />?)#",
				"<code><span style=\"color: #$1\">",
				$code
			);
		}
		if ($added_end_tag) {
			$code = str_replace("?&gt;</span></code>", "</span></code>", $code);
			// Wait a minute. It fails highlighting? Stupid highlighter.
			$code = str_replace("?&gt;</code>", "</code>", $code);
		}
		$code = preg_replace("#<span style=\"color: \#([A-Z0-9]{6})\"></span>#", "", $code);
		$code = str_replace("<code>", "<div dir=\"ltr\"><code>", $code);
		$code = str_replace("</code>", "</code></div>", $code);
		$code = preg_replace("# *$#", "", $code);
		if ($bare_return) {
			return $code;
		}

		// Send back the code all nice and pretty
		return "<div class=\"codeblock phpcodeblock\"><div class=\"title\">" .
		$php_code . "\n</div><div class=\"body\">" .
		$code . "</div></div>\n";
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function handleSizeCallback($matches)
	{
		return $this->myCodeHandleSize($matches[1], $matches[2]);
	}

	/**
	 * @param int    $size
	 * @param string $text
	 *
	 * @return string
	 */
	private function myCodeHandleSize($size, $text)
	{
		$size = (int)$size + 10;
		if ($size > 50) {
			$size = 50;
		}
		$text = "<span style=\"font-size: {$size}pt;\">" . str_replace("\'", "'", $text) . "</span>";

		return $text;
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parsePostQuotesCallback1($matches)
	{
		return $this->parsePostQuotes($matches[4], $matches[2] . $matches[3]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parsePostQuotesCallback2($matches)
	{
		return $this->parsePostQuotes($matches[4], $matches[2] . $matches[3], true);
	}

	/**
	 * @param string $message
	 * @param string $username
	 * @param bool   $text_only
	 *
	 * @return string
	 */
	private function parsePostQuotes($message, $username, $text_only = false)
	{
		$linkback = $date = "";
		$message = trim($message);
		$message = preg_replace("#(^<br(\s?)(\/?)>|<br(\s?)(\/?)>$)#i", "", $message);
		if (!$message) {
			return '';
		}
		$username .= "'";
		$delete_quote = true;
		if (!empty($this->postURL)) {
			preg_match("#pid=(?:&quot;|\"|')?([0-9]+)[\"']?(?:&quot;|\"|')?#i", $username, $match);
			if (isset($match[1]) && (int)$match[1]) {
				$pid = (int)$match[1];
				$url = $this->getPostURL($pid);
				$linkback = " <a href=\"{$url}\" class=\"quote_linkback\">[ -> ]</a>";
				$username = preg_replace(
					"#(?:&quot;|\"|')? pid=(?:&quot;|\"|')?[0-9]+[\"']?(?:&quot;|\"|')?#i",
					'',
					$username
				);
				$delete_quote = false;
			}
			unset($match);
		}
		preg_match("#dateline=(?:&quot;|\"|')?([0-9]+)(?:&quot;|\"|')?#i", $username, $match);
		if (isset($match[1]) && (int)$match[1]) {
			if ($match[1] < time()) {
				$postdate = $this->parseDate((int)$match[1]);
				$date = " ({$postdate})";
			}
			$username = preg_replace(
				"#(?:&quot;|\"|')? dateline=(?:&quot;|\"|')?[0-9]+(?:&quot;|\"|')?#i",
				'',
				$username
			);
			$delete_quote = false;
		}
		if ($delete_quote) {
			$username = substr($username, 0, strlen($username) - 1);
		}

		if ($this->allowHtml) {
			$username = htmlspecialchars($username);
		}
		$wrote = trans('parser::parser.wrote');
		if ($text_only) {
			return "\n{$username} {$wrote}{$date}\n--\n{$message}\n--\n";
		} else {
			$span = "";
			if (!$delete_quote) {
				$span = "<span>{$date}</span>";
			}

			return "<blockquote><cite>{$span}{$username} {$wrote}{$linkback}</cite>{$message}</blockquote>\n";
		}
	}

	/**
	 * @param int $pid
	 *
	 * @return string
	 */
	private function getPostURL($pid)
	{
		if (is_callable($this->postURL)) {
			return call_user_func($this->postURL, $pid);
		}

		return str_replace(':pid', $pid, $this->postURL);
	}

	/**
	 * @param int $time
	 *
	 * @return string
	 */
	private function parseDate($time)
	{
		if (is_callable($this->dateFormat)) {
			return call_user_func($this->dateFormat, $time);
		}

		return date($this->dateFormat, $time);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseCodeCallback($matches)
	{
		return $this->parseCode($matches[1], true);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parsePhpCallback($matches)
	{
		return $this->parsePhp($matches[1], false, true);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageCallback($matches)
	{
		return $this->parseImage($matches[2]);
	}

	/**
	 * @param string $url
	 * @param array  $dimensions
	 * @param string $align
	 *
	 * @return string
	 */
	private function parseImage($url, $dimensions = array(), $align = '')
	{
		$url = trim($url);
		$url = str_replace("\n", "", $url);
		$url = str_replace("\r", "", $url);

		if ($this->allowHtml) {
			$url = $this->fixHtml($url);
		}
		$css_align = "";
		if ($align == "right") {
			$css_align = " style=\"float: right;\"";
		} else {
			if ($align == "left") {
				$css_align = " style=\"float: left;\"";
			}
		}
		$alt = basename($url);
		if (strlen($alt) > 55) {
			$alt = htmlspecialchars_decode($alt);
			$alt = substr($alt, 0, 40) . "..." . substr($alt, -10);
			$alt = htmlspecialchars($alt);
		}
		$alt = trans('parser::parser.posted_image', ['alt' => $alt]);
		if (!empty($dimensions) && $dimensions[0] > 0 && $dimensions[1] > 0) {
			return "<img src=\"{$url}\" width=\"{$dimensions[0]}\" height=\"{$dimensions[1]}\" border=\"0\" alt=\"{$alt}\"{$css_align} />";
		} else {
			return "<img src=\"{$url}\" border=\"0\" alt=\"{$alt}\"{$css_align} />";
		}
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageCallback2($matches)
	{
		return $this->parseImage($matches[4], array($matches[1], $matches[2]));
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageCallback3($matches)
	{
		return $this->parseImage($matches[3], array(), $matches[1]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageCallback4($matches)
	{
		return $this->parseImage($matches[5], array($matches[1], $matches[2]), $matches[3]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageDisabledCallback($matches)
	{
		return $this->parseImageDisabled($matches[2]);
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	private function parseImageDisabled($url)
	{
		$url = trim($url);
		$url = str_replace("\n", "", $url);
		$url = str_replace("\r", "", $url);
		$url = str_replace("\'", "'", $url);
		$image = trans('parser::parser.posted_image', ['alt' => $this->parseUrl($url)]);

		return $image;
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseUrlCallback1($matches)
	{
		if (!isset($matches[3])) {
			$matches[3] = '';
		}

		return $this->ParseUrl($matches[1] . $matches[2], $matches[3]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseUrlCallback2($matches)
	{
		if (!isset($matches[2])) {
			$matches[2] = '';
		}

		return $this->ParseUrl($matches[1], $matches[2]);
	}

	/**
	 * @param string $url
	 * @param string $name
	 *
	 * @return string
	 */
	private function parseUrl($url, $name = "")
	{
		if (!preg_match("#^[a-z0-9]+://#i", $url)) {
			$url = "http://" . $url;
		}

		if ($this->allowHtml) {
			$url = $this->fixHtml($url);
		}
		$fullurl = $url;
		if (!$name) {
			$name = $url;
		}
		if ($name == $url && $this->shorten_urls) {
			if (strlen($url) > 55) {
				$name = substr($url, 0, 40) . "..." . substr($url, -10);
				$name = htmlspecialchars($name);
			}
		}
		$nofollow = '';
		if ($this->nofollow_on) {
			$nofollow = " rel=\"nofollow\"";
		}
		// Fix some entities in URLs
		$entities = array(
			'$' => '%24',
			'&#36;' => '%24',
			'^' => '%5E',
			'`' => '%60',
			'[' => '%5B',
			']' => '%5D',
			'{' => '%7B',
			'}' => '%7D',
			'"' => '%22',
			'<' => '%3C',
			'>' => '%3E',
			' ' => '%20'
		);
		$fullurl = str_replace(array_keys($entities), array_values($entities), $fullurl);
		$name = preg_replace("#&amp;\#([0-9]+);#si", "&#$1;", $name); // Fix & but allow unicode
		$link = "<a href=\"$fullurl\" target=\"_blank\"{$nofollow}>$name</a>";

		return $link;
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageDisabledCallback2($matches)
	{
		return $this->parseImageDisabled($matches[4]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageDisabledCallback3($matches)
	{
		return $this->parseImageDisabled($matches[3]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseImageDisabledCallback4($matches)
	{
		return $this->parseImageDisabled($matches[5]);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseEmailCallback($matches)
	{
		if (!isset($matches[2])) {
			$matches[2] = '';
		}

		return $this->parseEmail($matches[1], $matches[2]);
	}

	/**
	 * @param string $email
	 * @param string $name
	 *
	 * @return string
	 */
	private function parseEmail($email, $name = "")
	{
		if (!$name) {
			$name = $email;
		}
		if (preg_match("/^([a-zA-Z0-9-_\+\.]+?)@[a-zA-Z0-9-]+\.[a-zA-Z0-9\.-]+$/si", $email)) {
			return "<a href=\"mailto:$email\">" . $name . "</a>";
		} elseif (preg_match("/^([a-zA-Z0-9-_\+\.]+?)@[a-zA-Z0-9-]+\.[a-zA-Z0-9\.-]+\?(.*?)$/si", $email)) {
			return "<a href=\"mailto:" . htmlspecialchars($email) . "\">" . $name . "</a>";
		} else {
			return $email;
		}
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseVideoCallback($matches)
	{
		return $this->parseVideo($matches[1], $matches[2]);
	}

	/**
	 * @param string $video
	 * @param string $url
	 *
	 * @return string
	 */
	private function parseVideo($video, $url)
	{
		if (empty($video) || empty($url)) {
			return "[video={$video}]{$url}[/video]";
		}
		$parsed_url = @parse_url(urldecode($url));
		if ($parsed_url == false) {
			return "[video={$video}]{$url}[/video]";
		}
		$fragments = array();
		if (isset($parsed_url['fragment'])) {
			$fragments = explode("&", $parsed_url['fragment']);
		}
		$queries = array();
		if (isset($parsed_url['query'])) {
			$queries = explode("&", $parsed_url['query']);
		}
		$input = array();
		foreach ($queries as $query) {
			// $value isn't always defined, eg facebook uses "&theater" which would throw an error
			@list($key, $value) = explode("=", $query);
			$key = str_replace("amp;", "", $key);
			$input[$key] = $value;
		}
		$path = explode('/', $parsed_url['path']);
		$local = $title = '';
		switch ($video) {
			case "dailymotion":
				list($id,) = explode("_", $path[2], 1); // http://www.dailymotion.com/video/fds123_title-goes-here
				break;
			case "metacafe":
				$id = $path[2]; // http://www.metacafe.com/watch/fds123/title_goes_here/
				$title = htmlspecialchars($path[3]);
				break;
			case "myspacetv":
				$title = htmlspecialchars($path[3]); // http://www.myspace.com/channel/video/fds/123
				$id = $path[4];
				break;
			case "facebook":
				$id = $input['v']; // http://www.facebook.com/video/video.php?v=123
				break;
			case "veoh":
				$id = $path[2]; // http://www.veoh.com/watch/123
				break;
			case "liveleak":
				$id = $input['i']; // http://www.liveleak.com/view?i=123
				break;
			case "yahoo":
				$id = $path[1]; // http://xy.screen.yahoo.com/fds-123.html
				// Support for localized portals
				$domain = explode('.', $parsed_url['host']);
				if ($domain[0] != 'screen' && preg_match('#^([a-z-]+)$#', $domain[0])) {
					$local = "{$domain[0]}.";
				}
				break;
			case "vimeo":
				$id = $path[1]; // http://vimeo.com/fds123
				break;
			case "youtube":
				if (!empty($fragments[0])) {
					$id = str_replace('!v=', '', $fragments[0]); // http://www.youtube.com/watch#!v=fds123
				} elseif (!empty($input['v'])) {
					$id = $input['v']; // http://www.youtube.com/watch?v=fds123
				} else {
					$id = $path[1]; // http://www.youtu.be/fds123
				}
				break;
			default:
				return "[video={$video}]{$url}[/video]";
		}
		if (empty($id)) {
			return "[video={$video}]{$url}[/video]";
		}
		$id = htmlspecialchars($id);
		$code = config('video_codes.' . $video);
		$code = str_replace([':id', ':local', ':title'], [$id, $local, $title], $code);

		return $code;
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseVideoDisabledCallback($matches)
	{
		return $this->parseVideoDisabled($matches[2]);
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	private function parseVideoDisabled($url)
	{
		$url = trim($url);
		$url = str_replace("\n", "", $url);
		$url = str_replace("\r", "", $url);
		$url = str_replace("\'", "'", $url);
		$video = trans('parser::parser.posted_video', ['alt' => $this->parseUrl($url)]);

		return $video;
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function autoUrlCallback($matches)
	{
		$external = '';
		// Allow links like http://en.wikipedia.org/wiki/PHP_(disambiguation) but detect mismatching braces
		while (substr($matches[3], -1) == ')') {
			if (substr_count($matches[3], ')') > substr_count($matches[3], '(')) {
				$matches[3] = substr($matches[3], 0, -1);
				$external = ')' . $external;
			} else {
				break;
			}
			// Example: ([...] http://en.wikipedia.org/Example_(disambiguation).)
			$last_char = substr($matches[3], -1);
			while ($last_char == '.' || $last_char == ',' || $last_char == '?' || $last_char == '!') {
				$matches[3] = substr($matches[3], 0, -1);
				$external = $last_char . $external;
				$last_char = substr($matches[3], -1);
			}
		}
		if ($matches[2] == 'www' || $matches[2] == 'ftp') {
			return "{$matches[1]}[url]{$matches[2]}.{$matches[3]}[/url]{$external}";
		} else {
			return "{$matches[1]}[url]{$matches[2]}://{$matches[3]}[/url]{$external}";
		}
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function parseListCallback($matches)
	{
		return $this->parseList($matches[3], $matches[2]);
	}

	/**
	 * @param string $message
	 * @param string $type
	 *
	 * @return string
	 */
	private function parseList($message, $type = "")
	{
		// No list elements? That's invalid HTML
		if (strpos($message, '[*]') === false) {
			$message = "[*]{$message}";
		}
		$message = preg_replace("#\s*\[\*\]\s*#", "</li>\n<li>", $message);
		$message .= "</li>";
		if ($type) {
			$list = "\n<ol type=\"$type\">$message</ol>\n";
		} else {
			$list = "<ul>$message</ul>\n";
		}
		$list = preg_replace("#<(ol type=\"$type\"|ul)>\s*</li>#", "<$1>", $list);

		return $list;
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 */
	private function prepareList($matches)
	{
		// Append number to identify matching list tags
		if (strcasecmp($matches[1], '[/list]') == 0) {
			$count = array_pop($this->list_elements);
			if ($count !== null) {
				return "[/list&{$count}]";
			} else {
				// No open list tag...
				return $matches[0];
			}
		} else {
			++$this->list_count;
			$this->list_elements[] = $this->list_count;
			if (!empty($matches[2])) {
				return "[list{$matches[2]}&{$this->list_count}]";
			} else {
				return "[list&{$this->list_count}]";
			}
		}
	}
}
