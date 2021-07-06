<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Segments\SqlSegment;
use PHPUnit\Framework\TestCase;

class SqlSegmentTest extends TestCase
{
    public function test_serializes_correctly()
    {
        $segment = new SqlSegment();
        $segment->setQuery('SELECT *')
            ->setDatabaseType('PostgreSQL')
            ->setDatabaseVersion('10.4')
            ->setDriverVersion('10')
            ->setPreparation('prepared')
            ->setUser('test')
            ->setUrl('pgsql://test@localhost');

        $serialized = $segment->jsonSerialize();

        $this->assertEquals('remote', $serialized['namespace']);
        $this->assertEquals('SELECT *', $serialized['sql']['sanitized_query']);
        $this->assertEquals('PostgreSQL', $serialized['sql']['database_type']);
        $this->assertEquals('10.4', $serialized['sql']['database_version']);
        $this->assertEquals('10', $serialized['sql']['driver_version']);
        $this->assertEquals('test', $serialized['sql']['user']);
        $this->assertEquals('prepared', $serialized['sql']['preparation']);
        $this->assertEquals('pgsql://test@localhost', $serialized['sql']['url']);
    }

    public function test_setting_start_and_end_time()
    {
        $segment = new SqlSegment();
        $segment->setDatabaseType('MySQL')
            ->setQuery('SELECT *')
            ->begin(1584448767.5)
            ->end(1);

        $serialized = $segment->jsonSerialize();

        $this->assertEquals(1584448766.5, $serialized['start_time']);
        $this->assertEquals(1584448767.5, $serialized['end_time']);
    }

    public function test_setting_end_time()
    {
        $segment = new SqlSegment();
        $segment->setDatabaseType('MySQL')
            ->setQuery('SELECT *')
            ->begin(1584448767.5);

        // wait
        sleep(1);

        // only add a 0.1 sec
        $segment->end(0.1);

        $serialized = $segment->jsonSerialize();

        $this->assertEquals(1584448767.4, $serialized['start_time']);
        $this->assertEquals(1584448767.5, $serialized['end_time']);
    }
}
