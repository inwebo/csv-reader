<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Iterate;

use Inwebo\Csv\Reader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
#[Group('Instantiate')]
class ReadTest extends TestCase
{
    private ?Reader $reader;

    public function setUp(): void
    {
        $this->reader = new Reader(WITH_HEADER, hasColName: true); /* @phpstan-ignore */
    }

    public function tearDown(): void
    {
        $this->reader = null;
    }

    public function testColName(): void
    {
        $this->assertIsArray($this->reader->getColsName());
        $this->assertEquals('Id', $this->reader->getColsName()[0]);
        $this->assertEquals('Firstname', $this->reader->getColsName()[1]);
        $this->assertEquals('Lastname', $this->reader->getColsName()[2]);
        $this->assertEquals('Email', $this->reader->getColsName()[3]);
    }

    public function testAt(): void
    {
        $headers = $this->reader->lineAt(0);
        $this->assertIsArray($headers);
        $this->assertEquals(1, $headers['Id']);
        $this->assertEquals('Charles', $headers['Firstname']);
        $this->assertEquals('de Gaulle', $headers['Lastname']);
        $this->assertEquals('', $headers['Email']);
    }

    public function testInvalidAt(): void
    {
        $line = $this->reader->lineAt(100);

        $this->assertFalse($line);
    }

    public function testCount(): void
    {
        $this->assertIsIterable($this->reader->lines());

        $lines = $this->reader->lines();

        $i = 0;

        while ($lines->valid()) {
            ++$i;
            $lines->next();
        }

        $this->assertEquals(8, $i);
    }

    public function testSanitizers(): void
    {
        $sanitize = function (array &$line) {
            $line['Id'] = (int) $line['Id'];
        };

        $this->reader->addSanitizer($sanitize);

        $line = $this->reader->lineAt(0);
        $this->assertIsArray($line);
        $this->assertEquals(1, $line['Id']);
    }

    public function testFilters(): void
    {
        $sanitize = function (array &$line) {
            $line['Id'] = (int) $line['Id'];
        };

        $filter = function (array $line): bool {
            return $line['Id'] % 2 === 0;
        };

        $this->reader->addSanitizer($sanitize);
        $this->reader->addFilter($filter);

        $count = 0;

        foreach ($this->reader->lines() as $line) {
            ++$count;
        }

        $this->assertEquals(4, $count);
    }
}
