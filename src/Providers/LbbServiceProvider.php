<?php declare(strict_types=1);

namespace STS\LBB\Providers;

use Illuminate\Support\ServiceProvider;
use STS\AwsEvents\Contracts\Eventful;
use STS\LBB\Lambda\Application as LambdaApplication;
use STS\LBB\Lambda\Contracts\Application as LambdaApplicationContract;
use STS\LBB\Lambda\Contracts\Registrar;
use STS\LBB\Lambda\Models\Context;
use STS\LBB\Lambda\Router;
use function base_path;
use function config_path;


class LbbServiceProvider extends ServiceProvider
{
    /**
     * Default path to laravel configuration file in the package
     *
     * @var string
     */
    protected $configPath = __DIR__.'/../../config/lbb.php';

    /**
     * Default path to publish the lambda routes file from.
     *
     * @var string
     */
    protected $routesPath = __DIR__.'/../../routes/lambda.php';

    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->configPath, 'lbb'
        );

        $this->app->alias(Eventful::class, 'lbb.lambda.events');
        $this->app->alias(Context::class, 'lbb.lambda.context');
        $this->app->singleton(
            LambdaApplicationContract::class,
            LambdaApplication::class
        );

        $this->app->alias(
            LambdaApplicationContract::class,
            'lbb.contract.application'
        );
        $this->app->alias(
            LambdaApplicationContract::class,
            'lbb.application'
        );

        $this->app->singleton(Registrar::class, Router::class);
        $this->app->alias(
            Registrar::class,
            'lbb.router'
        );
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([$this->configPath => config_path('lbb.php')],
            'bref-configuration');

        $this->publishes([$this->routesPath => base_path('routes/lambda.php')],
            'lbb-core-routes');

    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     *
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);
        $this->app['config']->set($key,
            $this->mergeConfig(require $path, $config));
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param  array  $original
     * @param  array  $merging
     *
     * @return array
     */
    protected function mergeConfig(array $original, array $merging)
    {
        $array = array_merge($original, $merging);
        foreach ($original as $key => $value) {
            if (! is_array($value)) {
                continue;
            }
            if (! Arr::exists($merging, $key)) {
                continue;
            }
            if (is_numeric($key)) {
                continue;
            }
            $array[$key] = $this->mergeConfig($value, $merging[$key]);
        }

        return $array;
    }


}