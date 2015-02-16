<?php namespace MyBB\Parser;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use MyBB\Parser\Badwords\CachingDecorator;
use MyBB\Parser\Parser\CustomCodes\CachingDecorator as CodeCachingDecorator;
use MyBB\Parser\Smilies\CachingDecorator as SmilieCachingDecorator;

class ParserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Using mergeConfig and don't publish the video codes config as it normally isn't necessary to edit the embed codes
        $this->mergeConfigFrom(
            __DIR__ . '/../resources/config/video_codes.php', 'video_codes'
        );

        // Bind a default instance of the HTMLPurifier and set the encoding to UTF-8 which shouldn't be a problem in most cases
        $this->app->bind(
            '\HTMLPurifier',
            function () {
                $config = \HTMLPurifier_Config::createDefault();
                $config->set('Core.Encoding', 'UTF-8');

                return new \HTMLPurifier($config);
            }
        );

        $this->app->bind(
            'MyBB\Parser\Badwords\IBadwordRepository',
            function (Application $app) {
                $repository = $app->make('MyBB\Parser\Badwords\BadwordRepository');
                $cache = $app->make('Illuminate\Contracts\Cache\Repository');

                return new CachingDecorator($repository, $cache);
            }
        );

        $this->app->bind(
            'MyBB\Parser\Smilies\ISmilieRepository',
            function (Application $app) {
                $repository = $app->make('MyBB\Parser\Smilies\SmilieRepository');
                $cache = $app->make('Illuminate\Contracts\Cache\Repository');

                return new SmilieCachingDecorator($repository, $cache);
            }
        );

        // Bind the CustomMyCode Repository to the BBCode Parser
        $this->app->when('MyBB\Parser\Parser\Bbcode')
                  ->needs('MyBB\Parser\Parser\CustomCodes\ICustomCodeRepository')
                  ->give(function (Application $app) {
                      $repository = $app->make('MyBB\Parser\Parser\CustomCodes\CustomMyCodeRepository');
                      $cache = $app->make('Illuminate\Contracts\Cache\Repository');

                      return new CodeCachingDecorator($repository, $cache);
                  });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'parser');

        $this->publishes([
                             __DIR__ . '/../resources/migrations/' => base_path('/database/migrations')
                         ], 'migrations');
    }
}
