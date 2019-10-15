<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Controllers;

use Laravel\Vapor\Contracts\LambdaResponse;
use Laravel\Vapor\Runtime\Handlers\LoadBalancedFpmHandler;
use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;

class VaporElb
{
    /** @var LoadBalancedFpmHandler */
    protected $elb;

    public function __construct(LoadBalancedFpmHandler $elb)
    {
        $this->elb = $elb;
    }

    public function handle(Event $event, Context $context): LambdaResponse
    {
        return $this->elb->handle($event->toArray());
    }
}