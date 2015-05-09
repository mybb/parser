<?php

/**
 * This file contains a list of embed codes for supported sites. Note that adding a new one here doesn't work as the
 * parser isn't smooth enough to detect it manually - yet.
 *
 * :id will be replaced by the video id
 * :title will be replaced by the htmlspecialchar'd video title
 * :local will be replaced by the local subdomain (atm only used by yahoo)
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

return [
	'dailymotion' => '<iframe frameborder="0" width="480" height="270" src="http://www.dailymotion.com/embed/video/:id"></iframe>',
	'facebook' => '<iframe src="https://www.facebook.com/video/embed?video_id=:id" width="625" height="350" frameborder="0"></iframe>',
	'liveleak' => '<iframe width="500" height="300" src="http://www.liveleak.com/ll_embed?i=:id" frameborder="0" allowfullscreen></iframe>',
	'metacafe' => '<iframe src="http://www.metacafe.com/embed/:id/" width="440" height="248" allowFullScreen frameborder=0></iframe>',
	'myspacetv' => '<iframe width="480" height="270" src="//media.myspace.com/play/video/:title-:id" frameborder="0" allowtransparency="true" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
	'veoh' => '<object width="410" height="341" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1446&permalinkId=:id&player=videodetailsembedded&videoAutoPlay=0&id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1446&permalinkId=:id&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="410" height="341" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>',
	'vimeo' => '<iframe src="http://player.vimeo.com/video/:id" width="500" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
	'yahoo' => '<iframe width="624" height="351" scrolling="no" frameborder="0" src="http://:localscreen.yahoo.com/:id?format=embed"></iframe>',
	'youtube' => '<iframe width="560" height="315" src="http://www.youtube.com/embed/:id" frameborder="0" allowfullscreen></iframe>',
];
