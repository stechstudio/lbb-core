<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Controllers;

use Laravel\Vapor\Contracts\LambdaResponse;
use Laravel\Vapor\Runtime\Handlers\FpmHandler;
use Laravel\Vapor\Runtime\Handlers\LoadBalancedFpmHandler;
use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;

class VaporAPIGateway
{
    /** @var LoadBalancedFpmHandler */
    protected $fpm;

    public function __construct(FpmHandler $fpm)
    {
        $this->fpm = $fpm;
    }

    public function handle(Event $event, Context $context): LambdaResponse
    {
        return $this->fpm->handle($event->toArray());
    }
}