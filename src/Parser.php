<?php
/**
 * Main parser, handles the parsing of MyCode or markdown, along with emoticons/smileys and media embeds.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace Mybb\Parser;

use s9e\TextFormatter\Configurator;

class Parser
{
    /**
     * The settings for the parser.
     *
     * @var array $settings The settings for the parser.
     */
    protected $settings = [];

    /**
     * The created text formatter instance.
     *
     * @var \s9e\TextFormatter\Parser $formatter
     */
    protected $formatter;

    /**
     * The created renderer instance.
     *
     * @var \s9e\TextFormatter\Renderer $renderer
     */
    protected $renderer;

    /**
     * Parser constructor.
     *
     * @param Configurator $textFormatterConfigurator Configurator for the text formatter.
     * @param array $settings THe settings to use for the parser.
     */
    public function __construct(Configurator $textFormatterConfigurator, array $settings = [])
    {
        $this->settings = $this->filterConfig($settings);

        $this->configureBaseFormatter($textFormatterConfigurator);

        $finalized = $textFormatterConfigurator->finalize();

        $this->formatter = $finalized['parser'];
        $this->renderer = $finalized['renderer'];
    }

    /**
     * Filter a config array to make sure it is valid and contains the minimum required elements.
     *
     * @param array $config The config to filter.
     *
     * @return array The filtered config.
     */
    private function filterConfig(array $config = [])
    {
        $config = array_merge([
            'formatter_type' => 'mycode',
        ], $config);

        if (!in_array(mb_strtolower($config['formatter_type']), ['mycode', 'markdown'])) {
            $config['formatter_type'] = 'mycode';
        }

        return $config;
    }

    private function configureBaseFormatter(Configurator &$configurator)
    {
        switch ($this->settings['formatter_type']) {
            case 'mycode':
                // TODO: Register custom BBCodes and supported base BBCodes.
                break;
            case 'markdown':
                $configurator->plugins->load('Litedown');
                break;
        }
    }

    public function parse($text, array $options = [])
    {
        $xml = $this->formatter->parse($text);
        return $this->renderer->render($xml);
    }
}
