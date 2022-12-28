<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Segments\SqlSegment;
use PHPUnit\Framework\TestCase;

class SqlSegmentTest extends TestCase
{
    public function testSerializesCorrectly()
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

    public function testSettingEndTime()
    {
        $segment = new SqlSegment();
        $segment->setDatabaseType('MySQL')
            ->setQuery('SELECT *')
            ->begin();

        // wait
        sleep(1);

        // only add a 0.1 sec
        $segment->end(0.1);

        $serialized = $segment->jsonSerialize();

        $this->assertEqualsWithDelta(
            0.1,
            $serialized['end_time'] - $serialized['start_time'],
            0.001
        );
    }
}
