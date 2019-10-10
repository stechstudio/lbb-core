<?php declare(strict_types=1);

namespace STS\LBB\Queue;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory as QueueManager;
use Laravel\Vapor\Queue\VaporWorker;
use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;

class LbbWorker extends VaporWorker
{

    public function __construct(
        QueueManager $manager,
        Dispatcher $events,
        ExceptionHandler $exceptions,
        callable $isDownForMaintenance
    ) {
        parent::__construct($manager, $events, $exceptions,
            $isDownForMaintenance);
    }

    public function handle(Event $event, Context $context): array
    {
        
    }


}