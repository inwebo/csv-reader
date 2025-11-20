<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Exception;

use Inwebo\Csv\Reader;
use Inwebo\CSV\Reader\Tests\Fixtures\Model\FilesTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
class ReaderExceptionTest extends TestCase
{
    use FilesTrait;

    public function testExceptionLinesToIsNull(): void
    {
        $reader = new Reader($this->getWithHeaderFile(), hasHeader: true);

        $lines = $reader->lines(null, 12);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $to parameter must be null when $from is null');
        $lines = $lines->current();
    }

    public function testExceptionLinesFromIsNull(): void
    {
        $reader = new Reader($this->getWithHeaderFile(), hasHeader: true);

        $lines = $reader->lines(1, null);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $from parameter must be null when $to is null');
        $lines = $lines->current();
    }

    public function testExceptionLinesFromIsGreaterThanTo(): void
    {
        $reader = new Reader($this->getWithHeaderFile(), hasHeader: true);

        $lines = $reader->lines(10, 5);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The $from parameter must be less than or equal to $to');
        $lines = $lines->current();
    }
}
