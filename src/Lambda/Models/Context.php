<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Models;

use Illuminate\Support\Collection;
use STS\AwsEvents\Contexts\Context as AwsEventsContext;
use function env;
use function strtolower;

class Context extends AwsEventsContext
{
    public static function fromArray(array $arrayContext): self
    {
        $contextCollection = new Collection($arrayContext);
        $context           = new static;

        $context->setFunctionName(env('AWS_LAMBDA_FUNCTION_NAME', ''));
        $context->setFunctionVersion(env('AWS_LAMBDA_FUNCTION_VERSION', ''));
        $context->setLogGroupName(env('AWS_LAMBDA_LOG_GROUP_NAME', ''));
        $context->setLogStreamName(env('AWS_LAMBDA_LOG_STREAM_NAME', ''));

        $context->setMemoryLimitInMb(env('AWS_LAMBDA_FUNCTION_MEMORY_SIZE',
            ''));
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