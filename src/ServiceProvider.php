<?php
/**
 * Parser service provider, registers singleton parser.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace Mybb\Parser;

use Illuminate\Contracts\Foundation\Application;
use s9e\TextFormatter\Configurator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'parser');


        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/parser'),
        ], 'lang');

        $this->publishes([
            __DIR__.'/../resources/config' => config_path('parser.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/migrations' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/../resources/config/parser.php', 'parser');

        $this->app->singleton(Parser::class, function(Application $app) {
            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app['config'];
            return new Parser($app->make(Configurator::class, $config->get('parser')));
        });
    }
}