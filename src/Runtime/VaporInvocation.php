<?php declare(strict_types=1);

namespace STS\LBB\Runtime;

use Laravel\Vapor\Runtime\LambdaInvocation;
use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function preg_match;
use function preg_split;
use function strlen;
use function strtolower;
use function trim;
use const CURLOPT_FAILONERROR;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_WRITEFUNCTION;

class VaporInvocation extends LambdaInvocation
{
    /**
     * Get the next Lambda invocation ID and body.
     *
     * @param  string  $apiUrl
     *
     * @return array
     */
    public static function next($apiUrl)
    {
        if (static::$handler == null) {
            static::$handler
                = curl_init("http://{$apiUrl}/2018-06-01/runtime/invocation/next");

            curl_setopt(static::$handler, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt(static::$handler, CURLOPT_FAILONERROR, true);
        }

        // Retrieve the Lambda invocation ID...
        $invocationId = '';
        // Retrieve the Lambda invocation Context...
        $invocationContext = [];
        curl_setopt(static::$handler, CURLOPT_HEADERFUNCTION,
            function ($ch, $header) use (&$invocationId, &$invocationContext) {
                if (! preg_match('/:\s*/', $header)) {
                    return strlen($header);
                }

                [$name, $value] = preg_split('/:\s*/', $header, 2);

                $invocationContext[strtolower(trim($name))] = trim($value);

                if (strtolower($name) === 'lambda-runtime-aws-request-id') {
                    $invocationId = trim($value);
                }

                return strlen($header);
            });

        // Retrieve the Lambda invocation event body...
        $body = '';

        curl_setopt(static::$handler, CURLOPT_WRITEFUNCTION,
            function ($ch, $chunk) use (&$body) {
                $body .= $chunk;

                return strlen($chunk);
            });

        curl_exec(static::$handler);

        static::ensureNoErrorsOccurred(
            $invocationId, $body
        );

        return [
            $invocationId, Event::fromJson($body),
            Context::fromArray($invocationContext)
        ];
    }
}