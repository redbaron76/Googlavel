<?php namespace Redbaron76\Googlavel;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class GooglavelServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('redbaron76/googlavel');

		// Load facade alias
		AliasLoader::getInstance()->alias(
			'Googlavel',
			'Redbaron76\Googlavel\Support\Facades\Googlavel'
		);		
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register the GoogleAPI class
		$this->app['Googlavel'] = $this->app->share(function($app)
		{
			return new Classes\Googlavel($app['session.store'], $app['config'], $app['redirect']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['googlavel'];
	}

}
