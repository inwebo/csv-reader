<?php

declare(strict_types=1);

namespace Inwebo\Csv\Model;

/**
 * @extends \SplQueue<callable>
 *
 * @phpstan-param $callable callable(array<int|string, mixed>):void
 *
 * @method void push(callable $callable)
 *
 * @phpstan-return callable(array<int|string, mixed>):void
 *
 * @method callable current()
 */
class NormalizersQueue extends \SplQueue
{
    /**
     * @param array<int|string, mixed> $row
     */
    public function normalize(array &$row): void
    {
        call_user_func_array($this->current(), [&$row]);
    }
}
