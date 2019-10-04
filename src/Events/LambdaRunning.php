<?php declare(strict_types=1);

/**
 * Package: laravel-bref-bridge
 * Create Date: 2019-03-02
 * Created Time: 10:03
 */

namespace STS\LBB\Events;

use STS\LBB\Lambda\Contracts\Application as LambdaApplication;

class LambdaRunning
{
    /** @var LambdaApplication */
    public $lambda;

    public function __construct(LambdaApplication $lambda)
    {
        $this->lambda = $lambda;
    }
}
