<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Expression;
use Napp\Xray\Segments\SqlSegment;

class DatabaseQueryCollector extends EventsCollector
{
    protected $bindingsEnabled = false;

    public function registerEventListeners(): void
    {
        $this->app->events->listen(QueryExecuted::class, function (QueryExecuted $query) {
            $sql = $query->sql instanceof Expression ? $query->sql->getValue() : $query->sql;
            $this->handleQueryReport($sql, $query->bindings, $query->time, $query->connection);
        });

        $this->checkForEnabledBindings();
    }

    protected function handleQueryReport(string $sql, array $bindings, float $time, Connection $connection): void
    {
        if ($this->bindingsEnabled) {
            $sql = $this->parseBindings($sql, $bindings, $connection);
        }

        $backtrace = $this->getBacktrace();

        $eventSuffix = sizeof($backtrace) > 0 ? ('at ' . $this->getCallerClass($backtrace)) : '(too deeply nested)';

        $this->current()->addSubsegment(
            (new SqlSegment())
                ->setName($connection->getName() . ' ' . $eventSuffix)
                ->setDatabaseType($connection->getDriverName())
                ->setQuery($sql)
                ->addMetadata('backtrace', $backtrace)
                ->begin()
                ->end($time / 1000)
        );
    }

    protected function checkForEnabledBindings(): void
    {
        $this->bindingsEnabled = config('xray.db_bindings');
    }

    private function parseBindings(string $sql, array $bindings, Connection $connection): string
    {
        $sql = str_replace(['%', '?'], ['%%', '%s'], $sql);

        $handledBindings = array_map(function ($binding) {
            if (is_null($binding) || is_numeric($binding)) {
                return $binding;
            }

            $value = str_replace(['\\', "'"], ['\\\\', "\'"], $binding);

            return "'{$value}'";
        }, $connection->prepareBindings($bindings));

        return vsprintf($sql, $handledBindings);
    }
}
