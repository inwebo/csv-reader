<?php

declare(strict_types=1);

namespace Inwebo\Csv\Model;

/**
 * @extends \SplQueue<callable>
 *
 * @phpstan-param $callable callable(array<int|string, mixed>):bool
 *
 * @method void push(callable $callable)
 *
 * @phpstan-return callable(array<int|string, mixed>):bool
 *
 * @method callable current()
 */
class FiltersQueue extends \SplQueue
{
    /**
     * @param array<int|string, mixed> $row
     */
    public function filter(array $row): bool
    {
        $filtered = call_user_func($this->current(), $row);

        return is_bool($filtered) ? $filtered : false;
    }
}
