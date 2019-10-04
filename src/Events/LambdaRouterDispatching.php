<?php declare(strict_types=1);

/**
 * Package: laravel-bref-bridge
 * Create Date: 2019-03-02
 * Created Time: 10:03
 */

namespace STS\LBB\Events;

use STS\LBB\Lambda\Contracts\Registrar;

class LambdaRouterDispatching
{
    /** @var Registrar */
    public $router;

    public function __construct(Registrar $router)
    {
        $this->router = $router;
    }
}
