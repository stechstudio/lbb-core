<?php declare(strict_types=1);

use STS\AwsEvents\Events\ApiGatewayProxyRequest;
use STS\AwsEvents\Events\ApplicationLoadBalancer;
use STS\AwsEvents\Events\Sqs;
use STS\LBB\Facades\LambdaRoute;
use STS\LBB\Lambda\Controllers\VaporAPIGateway;
use STS\LBB\Lambda\Controllers\VaporElb;
use STS\LBB\Lambda\Controllers\VaporWorker;

LambdaRoute::register(
    Sqs::class,
    VaporWorker::class);

LambdaRoute::register(
    ApplicationLoadBalancer::class,
    VaporElb::class
);

LambdaRoute::register(
    ApiGatewayProxyRequest::class,
    VaporAPIGateway::class
);