<?php

declare(strict_types=1);

namespace Inwebo\CSV\Reader\Tests\Model;

use Inwebo\Csv\Model\NormalizersQueue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NormalizersQueue::class)]
class NormalizersQueueTest extends TestCase
{
    public function testFilter(): void
    {
        $queue = new NormalizersQueue();
        $queue->push(fn (array $row) => $row);
        $queue->rewind();
        $row = [1, 2, 3];
        $queue->normalize($row);
        $this->assertIsArray($row);
    }
}
