<?php declare(strict_types=1);

namespace STS\LBB\Facades;

/**
 * @method static array dispatch(Event $event, Context $context)
 * @method static bool hasController(string $eventName)
 * @method static Registrar forget(string $eventName)
 * @method static Registrar register(string $eventName, callable $controller)
 * @method static Registrar registerConfiguredControllers(array $config)
 * @method static void registerFromFile(string $routes)
 */
class LambdaRoute extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lbb.router';
    }
}