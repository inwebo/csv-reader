<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Iterate;

use Inwebo\Csv\Reader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
#[Group('csv')]
#[Group('without-header')]
class ReadWithoutHeaderTest extends TestCase
{
    private ?Reader $reader;

    public function setUp(): void
    {
        $this->reader = new Reader(WITHOUT_HEADER, hasColName: false); /* @phpstan-ignore */
    }

    public function tearDown(): void
    {
        $this->reader = null;
    }

    public function testLineAt(): void
    {
        $headers = $this->reader->lineAt(1);

        $this->assertIsArray($headers);
        $this->assertEquals(2, $headers[0]);
        $this->assertEquals('Georges', $headers[1]);
        $this->assertEquals('Pompidou', $headers[2]);
        $this->assertEquals('', $headers[3]);
    }

    public function testMapping(): void
    {
        $this->reader
            ->mapIndexToColName(0, 'Id')
            ->mapIndexToColName(1, 'Firstname')
            ->mapIndexToColName(2, 'Lastname')
            ->mapIndexToColName(3, 'Email')
        ;

        $line = $this->reader->lineAt(0);

        $this->assertIsArray($line);

        $this->assertEquals(1, $line['Id']);
        $this->assertEquals('Charles', $line['Firstname']);
        $this->assertEquals('de Gaulle', $line['Lastname']);
        $this->assertEquals('', $line['Email']);
    }
}
