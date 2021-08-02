<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Expression;
use Napp\Xray\Segments\SqlSegment;

class DatabaseQueryCollector extends EventsCollector
{
    protected $bindingsEnabled = false;
    protected $eraseQuery = false;

    public function registerEventListeners(): void
    {
        $this->app->events->listen(QueryExecuted::class, function (QueryExecuted $query) {
            try {
                $sql = $query->sql instanceof Expression ? $query->sql->getValue() : $query->sql;
                $this->handleQueryReport($sql, $query->bindings, $query->time, $query->connection);
            } catch (Exception $e) {
                $this->handleException($e);
            }
        });

        $this->bindingsEnabled = config('xray.db_bindings');
        $this->eraseQuery = config('xray.db_erase_query');
    }

    public function handleQueryReport(string $sql, array $bindings, float $time, Connection $connection): void
    {
        if ($this->eraseQuery) {
            $sql = '';
        } else if ($this->bindingsEnabled) {
            $sql = $this->parseBindings($sql, $bindings, $connection);
        }

        $backtrace = $this->getBacktrace();
        $this->current()->addSubsegment(
            (new SqlSegment())
                ->setName($connection->getName())
                ->setDatabaseType($connection->getDriverName())
                ->setQuery($sql)
                ->addMetadata('backtrace', $backtrace)
                ->addAnnotation('controller', $this->getCallerClass($backtrace))
                ->begin()
                ->end($time / 1000)
        );
    }

    private function parseBindings(string $sql, array $bindings, Connection $connection): string
    {
        if (substr_count($sql, '?') != count($bindings)) {
            return $sql;
        }

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
