<?php namespace Mpedrera\Themify;

use Illuminate\Support\ServiceProvider;
use Mpedrera\Themify\Resolver\Resolver;
use Mpedrera\Themify\Finder\ThemeViewFinder;
use Mpedrera\Themify\Filter\ThemeFilter;

class ThemifyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->package('mpedrera/themify', 'themify');

		$this->registerResolver();
		$this->registerViewFinder();
		$this->registerMainClass();
		$this->registerThemeFilter();
	}

	/**
	 * Add a package before filter that gets executed for every request.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app['router']->filter('themify.resolve', 'ThemeFilter');
		$this->app['router']->when('*', 'themify.resolve');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

	/**
	 * Register Themify class in IoC container.
	 * 
	 * @return void
	 */
	protected function registerMainClass()
	{
		$this->app['themify'] = $this->app->share(function($app)
		{
			return new Themify(
				$app['themify.resolver'],
				$app['view.finder'],
				$app['events'],
				$app['config']
			);
		});
	}

	/**
	 * Register Mpedrera\Themify\Resolver\Resolver in IoC container.
	 * 
	 * @return void
	 */
	protected function registerResolver()
	{
		$this->app->bindShared('themify.resolver', function($app)
		{
			return new Resolver($app);
		});
	}

	/**
	 * Register ThemeViewFinder class.
	 * It will override Laravel default's ViewFinder
	 * to provide functionality for searching theme views.
	 *
	 * @return void
	 */
	protected function registerViewFinder()
	{
		$this->app->bindShared('view.finder', function($app)
		{
			$paths = $app['config']['view.paths'];

			return new ThemeViewFinder($app['files'], $paths);
		});
	}


	/**
	 * Register Mpedrera\Themify\Filter\ThemeFilter.
	 *
	 * @return void
	 */
	protected function registerThemeFilter()
	{
		$this->app->bind('ThemeFilter', function($app)
		{
			return new ThemeFilter($app['themify'], $app['events']);
		});
	}
}