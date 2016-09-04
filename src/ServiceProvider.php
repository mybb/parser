<?php
/**
 * Parser service provider, registers singleton parser.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\Application;
use MyBB\Parser\Database\Repositories\BadWordRepositoryInterface;
use MyBB\Parser\Database\Repositories\Decorators\BadWordCachingDecorator;
use MyBB\Parser\Database\Repositories\Eloquent\BadWordRepository;
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
        $this->mergeConfigFrom(__DIR__.'/../resources/config/parser.php', 'parser');

        $this->app->bind(BadWordRepositoryInterface::class, function (Application $app) {
            return new BadWordCachingDecorator(
                $app->make(BadWordRepository::class),
                $app->make(Repository::class)
            );
        });

        $this->app->singleton(Parser::class, function (Application $app) {
            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app['config'];
            return new Parser(
                $app['translator'],
                $app->make(BadWordRepositoryInterface::class),
                $app->make(Configurator::class),
                $config->get('parser')
            );
        });
    }
}
