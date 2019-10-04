<?php declare(strict_types=1);

namespace STS\LBB\Lambda;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use STS\AwsEvents\Events\ApiGatewayProxyRequest;
use STS\AwsEvents\Events\CloudformationCreateRequest;
use STS\AwsEvents\Events\Cloudfront;
use STS\AwsEvents\Events\CloudwatchLogs;
use STS\AwsEvents\Events\CognitoSync;
use STS\AwsEvents\Events\Config;
use STS\AwsEvents\Events\DynamodbUpdate;
use STS\AwsEvents\Events\Event;
use STS\AwsEvents\Events\IotButton;
use STS\AwsEvents\Events\KinesisDataFirehouse;
use STS\AwsEvents\Events\KinesisDataStreams;
use STS\AwsEvents\Events\Lex;
use STS\AwsEvents\Events\S3Delete;
use STS\AwsEvents\Events\S3Put;
use STS\AwsEvents\Events\ScheduledEvent;
use STS\AwsEvents\Events\SesEmailReceiving;
use STS\AwsEvents\Events\Sns;
use STS\AwsEvents\Events\Sqs;
use STS\LBB\Events\LambdaRouterDispatched;
use STS\LBB\Events\LambdaRouterDispatching;
use STS\LBB\Exceptions\InvalidEventController;
use STS\LBB\Lambda\Contracts\Registrar;
use STS\LBB\Lambda\Contracts\Registrar as LambdaRegistrarContract;
use STS\LBB\Lambda\Models\Context;
use function call_user_func;
use function class_exists;
use function file_exists;
use function get_class;
use function is_array;
use function is_callable;
use function sprintf;

class Router implements LambdaRegistrarContract
{
    /**
     * All of the AWS Events supported by the router.
     *
     * @var array
     */
    public static $awsEvents
        = [
            CloudwatchLogs::class,
            CognitoSync::class,
            Lex::class,
            ApiGatewayProxyRequest::class,
            CloudformationCreateRequest::class,
            Config::class,
            IotButton::class,
            KinesisDataFirehouse::class,
            ScheduledEvent::class,
            Cloudfront::class,
            S3Delete::class,
            S3Put::class,
            DynamodbUpdate::class,
            KinesisDataStreams::class,
            SesEmailReceiving::class,
            Sqs::class,
            Sns::class,
        ];

    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $laravelEventDispatcher;
    /**
     * The route collection .
     *
     * @var Collection
     */
    protected $routes;

    /**
     * The container
     *
     * @var \Illuminate\Foundation\Application
     */
    private $app;


    /**
     * Create a new Router instance.
     */
    public function __construct(
        Application $app
    ) {
        $this->routes = new Collection;
        $this->app    = $app;
    }

    /**
     * Dispatch a new event to be routed to the appropriate controller
     */
    public function dispatch(Event $event, Context $context): array
    {
        event(new LambdaRouterDispatching($this));

        $awsEvent = get_class($event);

        if (! $this->hasController($awsEvent)) {
            throw new InvalidEventController('No controller registered for '
                .$awsEvent);
        }
        $result = call_user_func(
            $this->routes->get($awsEvent),
            $event,
            $context
        );

        $result = is_array($result) ? $result : ['result' => $result];

        event(new LambdaRouterDispatched($this));

        return $result;
    }

    /**
     * Check if an event (by name) has a controller registered.
     */
    public function hasController(string $eventName): bool
    {
        return $this->routes->has($eventName);
    }

    /**
     * Forget a registered route
     */
    public function forget(string $eventName
    ): LambdaRegistrarContract {
        $this->routes->forget($eventName);
    }

    public function registerFromFile(string $routes): void
    {
        if (file_exists($routes)) {
            $router = $this;
            require_once $routes;
        }
    }

    /**
     * Registers a controller for a particular event by name.
     *
     * @param  mixed  $controller
     */
    public function register(string $event, $controller): Registrar
    {
        if (! is_callable($controller)) {
            if (! class_exists($controller)) {
                throw new InvalidEventController(sprintf('[%s] -> [%s] is invalid.',
                    $event, $controller));
            }

            $controller = [$this->app->make($controller), 'handle'];
        }
        $this->routes->put($event, $controller);

        return $this;
    }

    /**
     * Register a series of routes based on a configuration array
     */
    public function registerConfiguredControllers(array $config): Registrar
    {
        foreach ($config as $eventName => $controller) {
            $this->register($eventName, $controller);
        }

        return $this;
    }
}