<?php

class ParserTests extends PHPUnit_Framework_TestCase
{
    public function testInitBasicMarkdownParser()
    {
        $parser = new \Mybb\Parser\Parser(new \s9e\TextFormatter\Configurator(), [
            'formatter_type' => 'markdown',
        ]);
        
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
}
