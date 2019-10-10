<?php declare(strict_types=1);

use STS\AwsEvents\Events;
use STS\LBB\Facades\LambdaRoute;
use STS\LBB\Lambda\Controllers;

LambdaRoute::register(
    Events\Sqs::class,
    Controllers\VaporWorker::class);

