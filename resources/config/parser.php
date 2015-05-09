<?php
/**
 * Default configuration for the parser
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

return [
	'allowbasicmycode'  => true,
	'allowsymbolmycode' => true,
	'allowlinkmycode'   => true,
	'allowemailmycode'  => true,
	'allowcolormycode'  => true,
	'allowsizemycode'   => true,
	'allowfontmycode'   => true,
	'allowalignmycode'  => true,
	'allowlistmycode'   => true,
	'allowimgcode'      => true,
	'allowvideocode'    => true,
	'shorten_urls'      => false,
	'nofollow_on'       => false,
	// String or callable
	'dateFormat'        => 'd.m.Y H:i:s',
	// String or callable
	'postURL'           => '',
];
