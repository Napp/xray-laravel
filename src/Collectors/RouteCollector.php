<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Routing\Events\RouteMatched;

class RouteCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        if($this->app instanceof \Laravel\Lumen\Application){
            $this->endSegment('laravel boot');
        }
        else{
            $this->app->booted(function () {
                $this->endSegment('laravel boot');
            });
        }

        // Time between route resolution and request handled
        $this->app['events']->listen(RouteMatched::class, function ($event) {
            $this->addSegment('request handled', null, ['controller' => $this->getController()]);
            $this->getSegment('route matching')
                ->addAnnotation('route', $event->route->getActionName() ?? 'unknown')
                ->end();
        });

        $this->app['events']->listen(RequestHandled::class, function () {
            // Some middlewares might return a response
            // before the RouteMatched has been dispatched
            if ($this->hasAddedSegment('request handled')) {
                $this->endSegment('request handled');
            }
        });
    }

    protected function getController(): ?string
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];

        $route = $router->current();
        $controller = $route ? $route->getActionName() : null;

        if ($controller instanceof \Closure) {
            return 'anonymous function';
        }
        if (is_object($controller)) {
            return 'instance of ' . get_class($controller);
        }
        if (is_array($controller) && 2 === count($controller)) {
            if (is_object($controller[0])) {
                $controller = get_class($controller[0]) . '->' . $controller[1];
            } else {
                $controller = $controller[0] . '::' . $controller[1];
            }
        } elseif (! is_string($controller)) {
            return null;
        }

        return $controller;
    }
}
