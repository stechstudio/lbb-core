<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Models;

use Illuminate\Support\Collection;
use STS\AwsEvents\Contexts\Context as AwsEventsContext;
use function config;
use function strtolower;

class Context extends AwsEventsContext
{
    public static function fromArray(array $arrayContext): self
    {
        $contextCollection = new Collection($arrayContext);
        $context           = new static;

        $context->setFunctionName(config('lbb.function.name'));
        $context->setFunctionVersion(config('lbb.function.version'));
        $context->setLogGroupName(config('lbb.logging.group'));
        $context->setLogStreamName(config('lbb.logging.stream'));

        $context->setMemoryLimitInMb(config('lbb.logging.memory_limit', ''));
        $context->setInvokedFunctionArn($contextCollection->get(strtolower('Lambda-Runtime-Invoked-Function-Arn'),
            ''));
        $context->setAwsRequestId($contextCollection->get('lambda-runtime-aws-request-id',
            ''));
        $context->setRuntimeDeadlineMs((int) $contextCollection->get(strtolower('Lambda-Runtime-Deadline-Ms'),
            0));
        $context->setXRayTraceId($contextCollection->get(strtolower('Lambda-Runtime-Trace-Id'),
            ''));

        return $context;
    }
}