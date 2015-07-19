<?php
/**
 * Parser to parse smiley images within messages.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser;

use MyBB\Parser\Database\Repositories\SmileyRepositoryInterface;
use MyBB\Parser\Parser\Renderers\SmileyRendererInterface;

class SmileyParser
{
	/**
	 * An array of smiley search and replacements, in the form [find =>
	 * replace].
	 *
	 * @var array $smileys
	 */
	protected $smileys = null;

	/**
	 * A repository to load smileys from.
	 *
	 * @var SmileyRepositoryInterface $smileyRepository ;
	 */
	protected $smileyRepository;

	/**
	 * Renderer to render smileys
	 *
	 * @var SmileyRendererInterface $smileyRenderer
	 */
	protected $smileyRenderer;

	/**
	 * Create a new smiley parser.
	 *
	 * @param SmileyRepositoryInterface $smileyRepository Repository to load
	 *                                                    smileys from.
	 * @param SmileyRendererInterface $smileyRenderer Render to render smileys.
	 */
	public function __construct(
		SmileyRepositoryInterface $smileyRepository,
		SmileyRendererInterface $smileyRenderer
	) {
		$this->smileyRepository = $smileyRepository;
		$this->smileyRenderer = $smileyRenderer;
	}

	/**
	 * Parse a message, replacing all smiley codes with their images.
	 *
	 * @param string $message The message to parse.
	 *
	 * @return string The parsed message.
	 */
	public function parse($message)
	{
		$this->assertSmileysLoaded();

		if (!empty($this->smileys)) {
			// TODO: This will need to handle Markdown URLs too...
			// We do not want to parse smileys inside URLs or inside quote
			$regex = "#\[(url(=[^\]]*)?\]|" . // URL MyCode
				"quote=([^\]]*)?\])|" . // Quote MyCode
				"(http|ftp)(s|):\/\/[^\s]*#i"; // Links

			preg_match_all(
				$regex,
				$message,
				$badMatches,
				PREG_PATTERN_ORDER
			);

			if (count($badMatches[0]) > 0) {
				$message = preg_replace(
					$regex,
					'<mybb-skip-smileys>',
					$message
				);
			}

			$message = str_ireplace(
				array_keys($this->smileys),
				array_values($this->smileys),
				$message
			);

			// If we matched any tags previously, swap them back in
			if (count($badMatches[0]) > 0) {
				$message = explode('<mybb-skip-smileys>', $message);
				$i = 0;

				foreach ($badMatches[0] as $match) {
					$message[$i] .= $match;
					$i++;
				}

				$message = implode("", $message);
			}
		}

		return $message;
	}

	/**
	 * Ensure that smiley replacements have been loaded.
	 */
	protected function assertSmileysLoaded()
	{
		if ($this->smileys === null) {
			$smileys = $this->smileyRepository->getParseableSmileys();
			$this->smileys = [];

			foreach ($smileys as $search => $replace) {
				$replacement = $this->smileyRenderer->render($search, $replace);
				$this->smileys[$search] = $replacement;
			}
		}
	}
}
