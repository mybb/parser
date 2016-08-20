<?php

use \Mockery as m;
use PHPUnit\Framework\TestCase;

class ParserTests extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    private function initTransMock(): \Illuminate\Translation\Translator
    {
        $trans = m::mock(\Illuminate\Translation\Translator::class);
        $trans->shouldReceive('trans')->andReturn('Guest');
        return $trans;
    }

    private function initBadWordsMock(): \MyBB\Parser\Database\Repositories\BadWordRepositoryInterface
    {
        $badWords = m::mock(\MyBB\Parser\Database\Repositories\BadWordRepositoryInterface::class);
        $badWords->shouldReceive('getAllForParsing')->andReturn([
            'fuck' => 'f**k',
            'shit' => 's***',
        ]);
        return $badWords;
    }

    public function testBasicMarkdownParser()
    {
        $parser = new \MyBB\Parser\Parser(
            $this->initTransMock(),
            $this->initBadWordsMock(),
            new \s9e\TextFormatter\Configurator(),
            [
                'formatter_type' => 'markdown',
            ]
        );
        
        $markdown = <<<EOT
# This is a simple test

* This is a list.
* It's only two items long.
EOT;

        $expected = <<<EOT
<h1>This is a simple test</h1>

<ul><li>This is a list.</li>
<li>It's only two items long.</li></ul>
EOT;

        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testWithBadWords()
    {
        $parser = new \MyBB\Parser\Parser(
            $this->initTransMock(),
            $this->initBadWordsMock(),
            new \s9e\TextFormatter\Configurator(),
            [
                'formatter_type' => 'markdown',
            ]
        );

        $markdown = <<<EOT
# This is a simple test

* Fuck.
* SHIT.
EOT;

        $expected = <<<EOT
<h1>This is a simple test</h1>

<ul><li>f**k.</li>
<li>s***.</li></ul>
EOT;

        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testWithEmoji()
    {
        $parser = new \MyBB\Parser\Parser(
            $this->initTransMock(),
            $this->initBadWordsMock(),
            new \s9e\TextFormatter\Configurator(),
            [
                'formatter_type' => 'markdown',
            ]
        );

        $markdown = <<<EOT
# This is a simple test \xF0\x9F\x98\x83
EOT;

        // We have to ignore code standards for this long line
        // @codingStandardsIgnoreStart
        $expected = <<<EOT
<h1>This is a simple test <img alt="\xF0\x9F\x98\x83" class="emoji" draggable="false" width="16" height="16" src="//twemoji.maxcdn.com/16x16/1f603.png"></h1>
EOT;
        // @codingStandardsIgnoreEnd

        $actual = $parser->parse($markdown);

        $this->assertEquals($expected, $actual);
    }

    public function testBasicMyCodeParser()
    {
        $parser = new \MyBB\Parser\Parser(
            $this->initTransMock(),
            $this->initBadWordsMock(),
            new \s9e\TextFormatter\Configurator(),
            [
                'formatter_type' => 'mycode',
            ]
        );

        $myCode = <<<EOT
[b]This is a simple test[/b]

[center]This is some centered text...[/center]
EOT;

        $expected = <<<EOT
<b>This is a simple test</b>

<div style="text-align:center">This is some centered text...</div>
EOT;

        $actual = $parser->parse($myCode);

        $this->assertEquals($expected, $actual);

    }
}
