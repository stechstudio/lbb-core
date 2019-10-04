<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Contracts;

use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;

interface Application
{
    /**
     * Run a lambda command.
     */
    public function run(Event $event, Context $context): array;

    /**
     * Get the output from the last event.
     */
    public function output(): array;
}