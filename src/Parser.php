<?php
/**
 * Main parser, handles the parsing of MyCode or markdown, along with emoticons/smileys and media embeds.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser;

use Illuminate\Translation\Translator;
use MyBB\Parser\Database\Repositories\BadWordRepositoryInterface;
use s9e\TextFormatter\Configurator;

class Parser
{
    /**
     * Translator instance.
     *
     * @var Translator $translator
     */
    protected $translator;

    /**
     * @var BadWordRepositoryInterface $badWords
     */
    protected $badWords;

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
     * @param Translator $translator Translator instance.
     * @param BadWordRepositoryInterface $badWords
     * @param Configurator $textFormatterConfigurator Configurator for the text formatter.
     * @param array $settings The settings to use for the parser.
     */
    public function __construct(
        Translator $translator,
        BadWordRepositoryInterface $badWords,
        Configurator $textFormatterConfigurator,
        array $settings = []
    ) {
        $this->translator = $translator;
        $this->badWords = $badWords;

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
            'enable_emoji' => true,
            'emoji_source' => 'Twemoji',
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
                $this->configureMyCode($configurator);
                break;
            case 'markdown':
                $configurator->plugins->load('Litedown');
                break;
        }

        $this->configureBadWords($configurator);
        $this->configureEmoji($configurator);

        // TODO: Smileys and custom MyCode

        $configurator->plugins->load('Autoimage');
        $configurator->plugins->load('Autoemail');
        $configurator->Autolink->matchWww = true;

        $configurator->MediaEmbed->createIndividualBBCodes = true;
    }

    private function configureMyCode(Configurator &$configurator)
    {
        $configurator->BBCodes->addFromRepository('ALIGN');
        $configurator->BBCodes->addFromRepository('B');
        $configurator->BBCodes->addFromRepository('CENTER');
        $configurator->BBCodes->addFromRepository('CODE');
        $configurator->BBCodes->addFromRepository('COLOR');
        $configurator->BBCodes->addFromRepository('EM');
        $configurator->BBCodes->addFromRepository('FONT');
        $configurator->BBCodes->addFromRepository('I');
        $configurator->BBCodes->addFromRepository('LEFT');
        $configurator->BBCodes->addFromRepository('LIST');
        $configurator->BBCodes->addFromRepository('QUOTE');
        $configurator->BBCodes->addFromRepository('RIGHT');
        $configurator->BBCodes->addFromRepository('S');
        $configurator->BBCodes->addFromRepository('SIZE');
        $configurator->BBCodes->addFromRepository('STRONG');
        $configurator->BBCodes->addFromRepository('U');
        $configurator->BBCodes->addFromRepository('URL');
    }

    private function configureBadWords(Configurator &$configurator)
    {
        foreach ($this->badWords->getAllForParsing() as $badWord => $replacement) {
            $configurator->Censor->add($badWord, $replacement);
        }
    }

    private function configureEmoji(Configurator &$configurator)
    {
        if ($this->settings['enable_emoji']) {
            switch ($this->settings['emoji_source']) {
                case 'EmojiOne':
                    $configurator->Emoji->useEmojiOne();
                    break;
                default:
                    $configurator->Emoji;
                    break;
            }
        }
    }

    public function parse(string $text, array $options = []): string
    {
        $options = array_merge([
            'username' => $this->translator->trans('parser::parser.guest_username'),
        ], $options);

        $xml = $this->formatter->parse($text);
        return $this->renderer->render($xml);
    }
}
