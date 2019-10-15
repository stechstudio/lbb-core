<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Controllers;

use Illuminate\Queue\WorkerOptions;
use InvalidArgumentException;
use Laravel\Vapor\Queue\VaporJob;
use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;
use function app;
use function base64_decode;
use function explode;
use function json_decode;
use function sprintf;
use function tap;

class VaporWorker
{

    /**
     * @var \Illuminate\Container\Container
     */
    private $laravel;

    public function __construct()
    {
        $this->laravel = app();
    }

    public function handle(Event $event, Context $context): array
    {
        $job = $event->get('Records')->first()->get('body');

        return $this->laravel->make('command.vapor.work')->runVaporJob(
            $this->marshalJob($job),
            'sqs',
            $this->gatherWorkerOptions()
        );
    }

    /**
     * Marshal the job with the given message ID.
     *
     * @param  array  $message
     *
     * @return \Laravel\Vapor\Queue\VaporJob
     */
    protected function marshalJob(array $message)
    {
        $normalizedMessage = $this->normalizeMessage($message);

        $queue = $this->worker->getManager()->connection('sqs');

        return new VaporJob(
            $this->laravel, $queue->getSqs(), $normalizedMessage,
            'sqs', $this->queueUrl($message)
        );
    }

    /**
     * Normalize the casing of the message array.
     *
     * @param  array  $message
     *
     * @return array
     */
    protected function normalizeMessage(array $message)
    {
        return [
            'MessageId'         => $message['messageId'],
            'ReceiptHandle'     => $message['receiptHandle'],
            'Body'              => $message['body'],
            'Attributes'        => $message['attributes'],
            'MessageAttributes' => $message['messageAttributes'],
        ];
    }

    /**
     * Get the decoded message payload.
     *
     * @return array
     */
    protected function message()
    {
        return tap(json_decode(base64_decode($this->argument('message')), true),
            function ($message) {
                if ($message === false) {
                    throw new InvalidArgumentException("Unable to unserialize message.");
                }
            });
    }

    /**
     * Get the queue URL from the given message.
     *
     * @param  array  $message
     *
     * @return string
     */
    protected function queueUrl(array $message)
    {
        $eventSourceArn = explode(':', $message['eventSourceARN']);

        return sprintf(
            'https://sqs.%s.amazonaws.com/%s/%s',
            $message['awsRegion'],
            $accountId = $eventSourceArn[4],
            $queue = $eventSourceArn[5]
        );
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->option('delay'), $memory = 512,
            $timeout = 0, $sleep = 0,
            $this->option('tries'), $this->option('force'),
            $stopWhenEmpty = false
        );
    }
}