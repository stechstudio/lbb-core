<?php declare(strict_types=1);

namespace STS\LBB\Lambda\Contracts;

use STS\AwsEvents\Events\Event;
use STS\LBB\Lambda\Models\Context;

interface Registrar
{
    /**
     * Registers and event controller.
     */
    public function register(
        string $event,
        $controller
    ): self;

    /**
     * Takes in a configuration and registers all the event controllers.
     */
    public function registerConfiguredControllers(array $config): self;

    /**
     * Remove an event:controller route from the routes.
     */
    public function forget(string $event): self;

    /**
     * Dispatch an event to it's controller
     */
    public function dispatch(Event $event, Context $context): array;

    /**
     * Determine if an event has a controller.
     */
    public function hasController(string $event): bool;

}