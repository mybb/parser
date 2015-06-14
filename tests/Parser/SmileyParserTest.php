<?php
/**
 * Unit tests for the smiley parser.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Tests\Parser;

use Mockery as m;
use MyBB\Parser\Parser\SmileyParser;

class SmileyParserTest extends \PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Simple test of a short message string.
	 */
	public function testParseSimple()
	{
		$expected = 'Hello World smile';
		$message = 'Hello World :)';

		$smileyRepo = m::mock('MyBB\Parser\Database\Repositories\SmileyRepositoryInterface');
		$smileyRepo->shouldReceive('getParseableSmileys')->andReturn(
			[
				':)' => 'smile',
			    ':(' => 'sad',
			]
		);

		$smileyParser = new SmileyParser($smileyRepo);

		$actual = $smileyParser->parse($message);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Slightly more complex tests with a URL in the message, to test the skipping of URLs.
	 */
	public function testParseWithUrl()
	{
		$expected = 'Head to http://mybb.com smile';
		$message = 'Head to http://mybb.com :)';

		$smileyRepo = m::mock('MyBB\Parser\Database\Repositories\SmileyRepositoryInterface');
		$smileyRepo->shouldReceive('getParseableSmileys')->andReturn(
			[
				':)' => 'smile',
				':/' => 'awkward',
			]
		);

		$smileyParser = new SmileyParser($smileyRepo);

		$actual = $smileyParser->parse($message);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Test against a longer message with several smileys.
	 */
	public function testParseLongerContent()
	{
		$expected = <<<EOT
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ultrices urna lorem, nec ultrices mauris congue vel. Mauris dictum mauris vel lorem sodales lobortis. Nullam gravida blandit vehicula. Quisque eget nisi quis ligula luctus scelerisque at non risus. Proin et lacinia est. Pellentesque ut lectus a est molestie ullamcorper. Sed ultrices est cursus urna imperdiet porta. Fusce in libero nec metus fringilla fermentum. Etiam tincidunt gravida faucibus. Vestibulum dapibus efficitur nisl id egestas. Nunc non massa dui. Sed porttitor metus quis lorem ullamcorper, egestas mollis elit suscipit. Donec tincidunt magna eget purus iaculis molestie. Ut quis augue egestas, egestas velit bibendum, porta mi. Quisque egestas urna leo, sit amet fermentum augue facilisis vitae. Donec rutrum elementum vestibulum.

Ut ultricies sem iaculis, lacinia augue congue, rutrum sapien. Ut nec ipsum non arcu convallis aliquam ut vel leo. In vel risus sem. Fusce quis ullamcorper nisi. Nulla facilisi. Maecenas placerat ipsum a nisi interdum molestie. Suspendisse at eros odio <laugh>.

Mauris dapibus tellus in erat rutrum viverra. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Pellentesque id facilisis justo. Nunc sit amet lacus tellus. Ut ut pellentesque ex. Fusce quis ex ultrices, consectetur enim at, vulputate dolor. Nullam pharetra, elit ut ullamcorper suscipit, orci tellus suscipit nisi, vel luctus mi orci eget velit. Phasellus ornare ipsum ac elit maximus, sit amet suscipit odio imperdiet. Integer eget vehicula ante. Fusce porttitor aliquet molestie. Nullam pulvinar nisi odio, maximus efficitur libero sagittis a. Sed quis venenatis nunc <smile>.

Aliquam in sem est. Quisque faucibus condimentum dui, eget volutpat erat porttitor ut. Etiam quis magna odio. Ut sollicitudin rutrum urna ac fringilla. Quisque porta lorem magna, eu malesuada risus volutpat et. Morbi pellentesque a mauris at gravida. Aenean consequat bibendum tempus. Nunc laoreet nisl id sem venenatis hendrerit. Aliquam lacinia nec ante sed venenatis. Ut porttitor ipsum nec maximus faucibus. Quisque non purus vel lacus ultricies aliquet. Donec maximus viverra mi, quis accumsan diam dictum eget. Nam auctor ullamcorper varius. Vivamus libero odio, porttitor quis nisi et, convallis sagittis dui. <awkward> Aenean ante enim, mattis id ligula eleifend, fringilla euismod felis. Aliquam ac ipsum hendrerit, posuere augue vel, gravida turpis.

In luctus dictum leo in pretium. Vestibulum nibh augue, pellentesque quis consectetur in, commodo et ex. Integer vitae mauris sem. Etiam odio libero, pretium ac pretium vel, commodo et quam. Sed ac sodales justo. Pellentesque nec justo non lectus volutpat mattis ut quis purus. Praesent tincidunt venenatis odio, et blandit ipsum congue eu. Nunc vitae luctus justo. Sed condimentum ligula sed dolor suscipit efficitur. Morbi mattis convallis justo quis laoreet. Donec id ex dapibus, euismod dui vel, eleifend massa. Sed dui nunc, vulputate sit amet rhoncus a, lacinia quis justo. Proin eu ornare lacus.
EOT;



		$message = <<<EOT
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ultrices urna lorem, nec ultrices mauris congue vel. Mauris dictum mauris vel lorem sodales lobortis. Nullam gravida blandit vehicula. Quisque eget nisi quis ligula luctus scelerisque at non risus. Proin et lacinia est. Pellentesque ut lectus a est molestie ullamcorper. Sed ultrices est cursus urna imperdiet porta. Fusce in libero nec metus fringilla fermentum. Etiam tincidunt gravida faucibus. Vestibulum dapibus efficitur nisl id egestas. Nunc non massa dui. Sed porttitor metus quis lorem ullamcorper, egestas mollis elit suscipit. Donec tincidunt magna eget purus iaculis molestie. Ut quis augue egestas, egestas velit bibendum, porta mi. Quisque egestas urna leo, sit amet fermentum augue facilisis vitae. Donec rutrum elementum vestibulum.

Ut ultricies sem iaculis, lacinia augue congue, rutrum sapien. Ut nec ipsum non arcu convallis aliquam ut vel leo. In vel risus sem. Fusce quis ullamcorper nisi. Nulla facilisi. Maecenas placerat ipsum a nisi interdum molestie. Suspendisse at eros odio :D.

Mauris dapibus tellus in erat rutrum viverra. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Pellentesque id facilisis justo. Nunc sit amet lacus tellus. Ut ut pellentesque ex. Fusce quis ex ultrices, consectetur enim at, vulputate dolor. Nullam pharetra, elit ut ullamcorper suscipit, orci tellus suscipit nisi, vel luctus mi orci eget velit. Phasellus ornare ipsum ac elit maximus, sit amet suscipit odio imperdiet. Integer eget vehicula ante. Fusce porttitor aliquet molestie. Nullam pulvinar nisi odio, maximus efficitur libero sagittis a. Sed quis venenatis nunc :).

Aliquam in sem est. Quisque faucibus condimentum dui, eget volutpat erat porttitor ut. Etiam quis magna odio. Ut sollicitudin rutrum urna ac fringilla. Quisque porta lorem magna, eu malesuada risus volutpat et. Morbi pellentesque a mauris at gravida. Aenean consequat bibendum tempus. Nunc laoreet nisl id sem venenatis hendrerit. Aliquam lacinia nec ante sed venenatis. Ut porttitor ipsum nec maximus faucibus. Quisque non purus vel lacus ultricies aliquet. Donec maximus viverra mi, quis accumsan diam dictum eget. Nam auctor ullamcorper varius. Vivamus libero odio, porttitor quis nisi et, convallis sagittis dui. :/ Aenean ante enim, mattis id ligula eleifend, fringilla euismod felis. Aliquam ac ipsum hendrerit, posuere augue vel, gravida turpis.

In luctus dictum leo in pretium. Vestibulum nibh augue, pellentesque quis consectetur in, commodo et ex. Integer vitae mauris sem. Etiam odio libero, pretium ac pretium vel, commodo et quam. Sed ac sodales justo. Pellentesque nec justo non lectus volutpat mattis ut quis purus. Praesent tincidunt venenatis odio, et blandit ipsum congue eu. Nunc vitae luctus justo. Sed condimentum ligula sed dolor suscipit efficitur. Morbi mattis convallis justo quis laoreet. Donec id ex dapibus, euismod dui vel, eleifend massa. Sed dui nunc, vulputate sit amet rhoncus a, lacinia quis justo. Proin eu ornare lacus.
EOT;


		$smileyRepo = m::mock('MyBB\Parser\Database\Repositories\SmileyRepositoryInterface');
		$smileyRepo->shouldReceive('getParseableSmileys')->andReturn(
			[
				':)' => '<smile>',
				':/' => '<awkward>',
			    ':D' => '<laugh>',
			]
		);

		$smileyParser = new SmileyParser($smileyRepo);

		$actual = $smileyParser->parse($message);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * Test parsing messages that contain special characters.
	 */
	public function testWithSpecialCharacters()
	{
		$expected = 'Testing µ Ä Á ς:Ðφ smile でした';
		$message = 'Testing µ Ä Á ς:Ðφ :) でした';

		$smileyRepo = m::mock('MyBB\Parser\Database\Repositories\SmileyRepositoryInterface');
		$smileyRepo->shouldReceive('getParseableSmileys')->andReturn(
			[
				':)' => 'smile',
				':/' => 'awkward',
			]
		);

		$smileyParser = new SmileyParser($smileyRepo);

		$actual = $smileyParser->parse($message);

		$this->assertEquals($expected, $actual);
	}
}
