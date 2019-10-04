<?php declare(strict_types=1);

namespace STS\LBB\Lambda;

use Illuminate\Events\Dispatcher;
use STS\AwsEvents\Contracts\Eventful;
use STS\AwsEvents\Events\Event;
use STS\LBB\Events\LambdaRunning;
use STS\LBB\Events\LambdaStarting;
use STS\LBB\Events\LambdaStopping;
use STS\LBB\Lambda\Contracts\Registrar;
use STS\LBB\Lambda\Models\Context;

class Application implements \STS\LBB\Lambda\Contracts\Application
{

    /**
     * Stores the results from routing the Lambda Event
     *
     * @var array
     */
    protected $output = [];
    /**
     * The event we are currently working on.
     *
     * @var Event
     */
    protected $currentEvent;
    /**
     * The context for the event we are currently working on.
     *
     * @var Context
     */
    protected $currentContext;
    /**
     * This is the Laravel Event dispatcher, do not confuse
     * it with the Lambda Event router.
     *
     * @var Dispatcher
     */
    private $laravelEventDispatcher;

    /**
     * This is the event router for Lambda events.
     *
     * @var Registrar
     */
    private $lambdaEventRouter;

    public function __construct(
        Dispatcher $laravelEventDispatcher,
        Registrar $lambdaEventRouter
    ) {
        $this->laravelEventDispatcher = $laravelEventDispatcher;
        $this->laravelEventDispatcher->dispatch(new LambdaStarting($this));
        $this->lambdaEventRouter = $lambdaEventRouter;
    }

    /**
     * Little debug helper until we sort out a Lambda exception Handler.
     */
    protected function logThrowables(\Throwable $t): \Throwable
    {
        Log::debug($t->getMessage());
        Log::debug($t->getTraceAsString());

        return $t;
    }

    /**
     * Returns the Lambda Router results.
     */
    public function output(): array
    {
        return $this->output;
    }

    /**
     * Run the application.
     * Then sends them off through the router and returns the results.
     */
    public function run(
        Event $event,
        Context $context
    ): array {
        $this->laravelEventDispatcher->dispatch(new LambdaRunning($this));

        try {
            $this->currentEvent = $event;
            app()->instance(Eventful::class, $this->currentEvent);
            app()->alias(Eventful::class, 'bref.lambda.event');
        } catch (\Throwable $t) {
            Log::error('Failed to convert event string to an event object.');
            throw $this->logThrowables($t);
        }

        try {
            $this->currentContext = $context;
            app()->instance(Context::class, $this->currentContext);
            app()->alias(Context::class, 'bref.lambda.context');
        } catch (\Throwable $t) {
            Log::error('Failed to convert context string to an context object.');
            throw $this->logThrowables($t);
        }

        try {
            $this->output
                = $this->lambdaEventRouter->dispatch($this->currentEvent,
                $this->currentContext);
        } catch (\Throwable $t) {
            Log::error('Failed to route the Event.');
            throw $this->logThrowables($t);
        }

        $this->laravelEventDispatcher->dispatch(new LambdaStopping($this));

        return $this->output();
    }
}