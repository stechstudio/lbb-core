<?php declare(strict_types=1);

namespace STS\LBB\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * @method static array toArray()
 * @method static \Tightenco\Collect\Support\Collection toCollection(int $options = 0)
 * @method static int count()
 * @method static string toJson(int $options = 0)
 * @method static string jsonSerialize()
 * @mixin Facade
 */
class Event extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lbb.lambda.events';
    }
}
