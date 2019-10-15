<?php declare(strict_types=1);

namespace STS\LBB\Runtime;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Laravel\Vapor\Runtime\Fpm\Fpm;
use Laravel\Vapor\Runtime\HttpHandlerFactory;
use Laravel\Vapor\Runtime\LambdaContainer;
use Laravel\Vapor\Runtime\LambdaRuntime;
use Laravel\Vapor\Runtime\Secrets;
use Laravel\Vapor\Runtime\StorageDirectories;
use STS\AwsEvents\Events\ApiGatewayProxyRequest;
use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;
use STS\LBB\Lambda\Runtime;
use function fwrite;
use function key_exists;
use const STDERR;

class VaporRuntime
{
    /** @var array */
    public static $secrets;

    /** @var Fpm */
    public static $fpm;

    /** @var int */
    public static $invocations = 0;

    /** @var LambdaRuntime */
    public static $lambdaRuntime;

    public static function handle(): void
    {
        self::handleSsmSecrets();
        self::handleFpmStart();
        self::handleConfigurationCache();;
        self::handleLambdaInvocations();
    }

    /*
    |--------------------------------------------------------------------------
    | Inject SSM Secrets Into Environment
    |--------------------------------------------------------------------------
    |
    | Next, we will inject any of the application's secrets stored in AWS
    | SSM into the environment variables. These variables may be a bit
    | larger than the variables allowed by Lambda which has a limit.
    |
    */
    public static function handleSsmSecrets(): void
    {
        if (key_exists('VAPOR_SSM_PATH', $_ENV)) {
            self::$secrets = Secrets::addToEnvironment(
                $_ENV['VAPOR_SSM_PATH'],
                json_decode($_ENV['VAPOR_SSM_VARIABLES'] ?? '[]', true),
                getenv('APP_ROOT').'/vaporSecrets.php'
            );
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Start PHP-FPM
    |--------------------------------------------------------------------------
    |
    | We need to boot the PHP-FPM process with the appropriate handler so it
    | is ready to accept requests. This will initialize this process then
    | wait for this socket to become ready before continuing execution.
    |
    */
    public static function handleFpmStart(): void
    {
        fwrite(STDERR, 'Preparing to boot FPM');
        self::$fpm = Fpm::boot(
            getenv('APP_ROOT').'/httpHandler.php', self::$secrets
        );
        fwrite(STDERR, 'Booted FPM');
    }

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | To give the application a speed boost, we are going to cache all of the
    | configuration files into a single file. The file will be loaded once
    | by the runtime then it will read the configuration values from it.
    |
    */
    public static function handleConfigurationCache(): void
    {
        with(require getenv('APP_ROOT').'/bootstrap/app.php', function ($app) {
            StorageDirectories::create();
            $app->useStoragePath(StorageDirectories::PATH);
            fwrite(STDERR, 'Caching Laravel configuration');
            $app->make(ConsoleKernelContract::class)->call('config:cache');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Listen For Lambda Invocations
    |--------------------------------------------------------------------------
    |
    | When using FPM, we will listen for Lambda invocations and proxy them
    | through the FPM process. We'll then return formatted FPM response
    | back to the user. We'll monitor FPM to make sure it is running.
    |
    */
    public static function handleLambdaInvocations(): void
    {
        self::$lambdaRuntime = Runtime::fromEnvironmentVariable();

        while (true) {
            self::$lambdaRuntime->nextInvocation(
                function (
                    string $invocationId,
                    Event $event,
                    Context $context
                ) {

                    if (ApiGatewayProxyRequest::supports($event)) {
                        self::$fpm->ensureRunning();

                        return HttpHandlerFactory::make($event->toArray())
                            ->handle($event->toArray())
                            ->toApiGatewayFormat();
                    }

                    return app()->make('lbb.router')
                        ->dispatch($event, $context);
                });


            LambdaContainer::terminateIfInvocationLimitHasBeenReached(
                ++self::$invocations, (int) ($_ENV['VAPOR_MAX_REQUESTS'] ?? 250)
            );
        }
    }

}