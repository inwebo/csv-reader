<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Iterate;

use Inwebo\Csv\Reader;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\HasReaderTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
#[Group('csv')]
#[Group('with-header')]
class ReadWithHeaderTest extends TestCase
{
    use FilesTrait;
    use HasReaderTrait;
    private ?Reader $reader;

    public function setUp(): void
    {
        $this->reader = new Reader($this->getWithHeaderFile(), hasHeader: true);
        $this->assertTrue($this->getReader()->hasHeader());
    }

    public function tearDown(): void
    {
        $this->reader = null;
    }

    public function testColName(): void
    {
        $this->assertIsArray($this->getReader()->getHeader());
        $this->assertEquals('Id', $this->getReader()->getHeader()[0]);
        $this->assertEquals('Firstname', $this->getReader()->getHeader()[1]);
        $this->assertEquals('Lastname', $this->getReader()->getHeader()[2]);
        $this->assertEquals('Email', $this->getReader()->getHeader()[3]);
    }

    public function testAt(): void
    {
        $headers = $this->getReader()->lineAt(0);
        $this->assertIsArray($headers);
        $this->assertEquals(1, $headers['Id']);
        $this->assertEquals('Charles', $headers['Firstname']);
        $this->assertEquals('de Gaulle', $headers['Lastname']);
        $this->assertEquals('', $headers['Email']);
    }

    public function testInvalidAt(): void
    {
        $line = $this->getReader()->lineAt(100);

        $this->assertFalse($line);
    }

    public function testCount(): void
    {
        $this->assertIsIterable($this->getReader()->lines());

        $lines = $this->getReader()->lines();

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

        $this->getReader()->addSanitizer($sanitize);

        $line = $this->getReader()->lineAt(0);
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

        $this->getReader()->addSanitizer($sanitize);
        $this->getReader()->addFilter($filter);

        $count = 0;

        foreach ($this->getReader()->lines() as $line) {
            $this->assertIsInt($line['Id']);
            $this->assertTrue($line['Id'] % 2 === 0);

            ++$count;
        }

        $this->assertEquals(4, $count);
    }
}
