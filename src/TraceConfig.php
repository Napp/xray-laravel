<?php

declare(strict_types=1);

namespace Napp\Xray;

use Napp\Xray\Segments\Trace;
use Illuminate\Http\Request;

class TraceConfig
{
    public string $url;
    public string $method;
    public string $client_ip;
    public string $trace_id;
    public string $service_name;
    public int $sample_percentage;
    protected array $annotations;

    /**
     * Build config for tracing
     *
     * ['request']             Request, it will set up "url", "method", "ip"
     *
     * ['url']                 string, default: $request->url()
     *
     * ['method']              string, default: $request->method()
     *
     * ['client_ip']           string, default: $request->getClientIp()
     *
     * ['service_name']        string, default: config('app.name')
     *
     * ['trace_id']            string, default: $_SERVER['HTTP_X_AMZN_TRACE_ID']
     *
     * ['sample_percentage']   int, 0~100, default: 100
     *
     * ['annotations']         array, key/value pair
     *
     * @param Request $request
     * @param array $options (See above)
     */
    public function __construct(array $options = [])
    {
        /** @var Request */
        $request = $options['request'];

        $this->url = is_null($request) ? $options['url'] : $request->url();
        $this->method = is_null($request) ? $options['method'] : $request->method();
        $this->client_ip = is_null($request) ? $options['client_ip'] : $request->getClientIp();
        $this->service_name = $options['service_name'] ?? config('app.name');
        $this->trace_id = $options['trace_id'] ?? $_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null;
        $this->sample_pct = $options['sample_pct'] ?? 100;
        $this->annotations = $options['annotations'];
    }

    /**
     * Set annotations
     *
     * @param Trace $trace
     * @return void
     */
    public function setAnnotations(Trace $trace): void
    {
        if (is_null($this->annotations)) return;

        /** @var String */
        foreach ($this->annotations as $key => $value) {
            $trace->addAnnotation($key, $value);
        }
    }
}
