<?php declare(strict_types=1);

/**
 * Package: laravel-bref-bridge
 * Create Date: 2019-02-27
 * Created Time: 16:20
 */

namespace STS\LBB\Events;

use STS\LBB\Lambda\Contracts\Application as LambdaApplication;

class LambdaStarting
{

    /** @var LambdaApplication */
    public $lambda;

    public function __construct(LambdaApplication $lambda)
    {
        $this->lambda = $lambda;
    }
}
