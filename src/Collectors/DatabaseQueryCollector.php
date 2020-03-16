<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Napp\Xray\Segments\SqlSegment;

class DatabaseQueryCollector extends EventsCollector
{
    protected $bindingsEnabled = false;

    public function registerEventListeners(): void
    {
        $this->app->events->listen(QueryExecuted::class, function (QueryExecuted $query) {
            $this->handleQueryReport($query->sql, $query->bindings, $query->time, $query->connection);
        });

        $this->bindingsEnabled = config('xray.db_bindings');
    }

    protected function handleQueryReport(string $sql, array $bindings, float $time, Connection $connection): void
    {
        if ($this->bindingsEnabled) {
            $sql = $this->parseBindings($sql, $bindings, $connection);
        }
        $this->current()->addSubsegment(
            (new SqlSegment())
                ->setName($connection->getName())
                ->setDatabaseType($connection->getDriverName())
                ->setQuery($sql)
                ->begin()
                ->end($time/1000)
        );
    }

    private function parseBindings(string $sql, array $bindings, Connection $connection): string
    {
        $sql = str_replace(['%', '?'], ['%%', '%s'], $sql);

        $handledBindings = array_map(function ($binding) {
            if (is_numeric($binding)) {
                return $binding;
            }

            $value = str_replace(['\\', "'"], ['\\\\', "\'"], $binding);

            return "'{$value}'";
        }, $connection->prepareBindings($bindings));

        return vsprintf($sql, $handledBindings);
    }
}