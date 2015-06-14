<?php
/**
 * Service provider for parser classes.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use MyBB\Parser\Database\Repositories\Decorators\BadWordCachingDecorator;
use MyBB\Parser\Database\Repositories\Decorators\CustomMyMyCodeCachingDecorator;
use MyBB\Parser\Database\Repositories\Decorators\SmileysCachingDecorator;

class ParserServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Using mergeConfig and don't publish the video codes config
		// as it normally isn't necessary to edit the embed codes
		$this->mergeConfigFrom(
			__DIR__ . '/../resources/config/video_codes.php',
			'video_codes'
		);

		$this->mergeConfigFrom(
			__DIR__ . '/../resources/config/parser.php',
			'parser'
		);

		$this->app->singleton('MyBB\Parser\Parser\SmileyParser');

		// Bind a default instance of the HTMLPurifier and set the encoding to UTF-8
		// which shouldn't be a problem in most cases
		$this->app->bind(
			'\HTMLPurifier',
			function () {
				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Core.Encoding', 'UTF-8');

				return new \HTMLPurifier($config);
			}
		);

		$this->app->bind(
			'MyBB\Parser\Database\Repositories\BadWordRepositoryInterface',
			function (Application $app) {
				$repository = $app->make(
					'MyBB\Parser\Database\Repositories\Eloquent\BadWordRepository'
				);
				$cache = $app->make('Illuminate\Contracts\Cache\Repository');

				return new BadWordCachingDecorator($repository, $cache);
			}
		);

		$this->app->bind(
			'MyBB\Parser\Database\Repositories\SmileyRepositoryInterface',
			function (Application $app) {
				$repository = $app->make(
					'MyBB\Parser\Database\Repositories\Eloquent\SmileyRepository'
				);
				$cache = $app->make('Illuminate\Contracts\Cache\Repository');

				return new SmileysCachingDecorator($repository, $cache);
			}
		);

		// Bind the CustomMyCode Repository to the BBCode Parser
		$this->app->when('MyBB\Parser\Parser\MyCode')
		          ->needs(
			          'MyBB\Parser\Database\Repositories\CustomMyCodeRepositoryInterface'
		          )
		          ->give(
			          function (Application $app) {
				          $repository = $app->make(
					          'MyBB\Parser\Database\Repositories\Eloquent\CustomMyCodeRepository'
				          );
				          $cache = $app->make(
					          'Illuminate\Contracts\Cache\Repository'
				          );

				          return new CustomMyMyCodeCachingDecorator(
					          $repository,
					          $cache
				          );
			          }
		          );
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'parser');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'parser');

		$this->publishes(
			[
				__DIR__ . '/../resources/migrations/' => base_path(
					'/database/migrations'
				)
			],
			'migrations'
		);

		$this->publishes(
			[
				__DIR__ . '/../resources/views/' => base_path(
					'resources/views/vendor/parser'
				),
			],
			'views'
		);

		$this->publishes(
			[
				__DIR__ . '/../resources/config/parser.php' => config_path(
					'parser.php'
				)
			],
			'config'
		);
	}
}
