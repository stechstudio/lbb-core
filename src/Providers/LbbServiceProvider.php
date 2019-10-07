<?php declare(strict_types=1);

namespace STS\LBB\Providers;

use Illuminate\Support\ServiceProvider;
use STS\AwsEvents\Contracts\Eventful;
use STS\LBB\Lambda\Application as LambdaApplication;
use STS\LBB\Lambda\Contracts\Application as LambdaApplicationContract;
use STS\LBB\Lambda\Contracts\Registrar;
use STS\LBB\Lambda\Models\Context;
use STS\LBB\Lambda\Router;


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
}