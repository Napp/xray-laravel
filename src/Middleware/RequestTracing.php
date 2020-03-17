<?php

declare(strict_types=1);

namespace Napp\Xray\Middleware;

use Closure;
use Napp\Xray\Xray;

class RequestTracing
{
    /**
     * @var \Napp\Xray\Xray
     */
    private $xray;

    public function __construct(Xray $xray)
    {
        $this->xray = $xray;
    }

    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Terminates a request/response cycle.
     */
    public function terminate($request, $response)
    {
        $this->xray->submitHttpTracer($response);
    }
}
