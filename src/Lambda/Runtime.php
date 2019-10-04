<?php declare(strict_types=1);

namespace STS\LBB\Lambda;

use Laravel\Vapor\Runtime\LambdaRuntime;
use STS\LBB\Runtime\VaporInvocation;
use Throwable;

class Runtime extends LambdaRuntime
{

    /**
     * Handle the next Lambda invocation.
     *
     * @param  callable  $callback
     *
     * @return void
     */
    public function nextInvocation(callable $callback)
    {
        [$invocationId, $event, $context]
            = VaporInvocation::next($this->apiUrl);

        $_ENV['AWS_REQUEST_ID'] = $invocationId;

        try {
            $this->notifyLambdaOfResponse($invocationId,
                $callback($invocationId, $event, $context));
        } catch (Throwable $error) {
            $this->handleException($invocationId, $error);

            exit(1);
        }
    }
}