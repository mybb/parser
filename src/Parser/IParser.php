<?php namespace MyBB\Parser\Parser;

interface IParser
{
    /**
     * Parse a message into a HTML string ready for display.
     *
     * @param string $message   The message to parse.
     * @param bool   $allowHTML Whether to allow HTML in the message.
     *
     * @return string The parsed string.
     */
    public function parse($message, $allowHTML);

    /**
     * @param $message
     *
     * @return string
     */
    public function parsePlain($message);

    /**
     * @param bool $allow
     */
    public function allowBasicCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowSymbolCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowLinkCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowEmailCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowColorCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowSizeCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowFontCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowAlignCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowImgCode($allow = true);

    /**
     * @param bool $allow
     */
    public function allowVideoCode($allow = true);

    /**
     * @param bool $short
     */
    public function shortenURLs($short = true);

    /**
     * @param bool $on
     */
    public function useNoFollow($on = true);

    /**
     * @param $format
     */
    public function setDateFormatting($format);

    /**
     * @param $url
     */
    public function setPostURL($url);
}
