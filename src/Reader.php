<?php

declare(strict_types=1);

namespace Inwebo\Csv;

/**
 * The Reader class extends \SplFileObject to provide a more convenient way to read and process CSV files.
 * It streamlines data handling by allowing you to process rows as associative arrays (if the file has a header),
 * apply custom filters to skip certain rows, and use sanitizers to clean or modify data.
 * This object-oriented approach makes CSV file processing more structured and manageable.
 */
class Reader extends \SplFileObject
{
    /** @var array<int, string> */
    private array $header = [];

    /**
     * @phpstan-var (callable(array<int|string, ?string> $line):bool)[]
     *
     * @var array<int, callable>
     */
    private array $filters = [];

    /**
     * @phpstan-var (callable(array<int|string, ?string> $line):bool)[]
     *
     * @var array<int, callable>
     */
    private array $sanitizers = [];

    /**
     * Creates a new instance of the Reader class and initializes the CSV file for processing.
     * It sets the file's flags to \SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD for proper CSV parsing.
     * If the $hasColName parameter is true, it reads the first row of the file to use as column headers for subsequent rows.
     *
     * @param string   $filename       The file to open
     * @param string   $mode           [optional] The mode in which to open the file. See {@see fopen} for a list of allowed modes.
     * @param bool     $useIncludePath [optional] Whether to search in the include_path for filename
     * @param resource $context        [optional] A valid context resource created with {@see stream_context_create}
     * @param bool     $hasHeader      [optional] parameter is true, it reads the first row of the file to use as column headers for subsequent rows
     *
     * @throws \LogicException   When the filename is a directory
     * @throws \RuntimeException When the filename cannot be opened
     *
     * @see SplFileObject
     */
    public function __construct(
        string $filename,
        string $mode = 'r',
        bool $useIncludePath = false,
        mixed $context = null,
        private readonly bool $hasHeader = true,
    ) {
        parent::__construct($filename, $mode, $useIncludePath, $context);
        $this->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD);

        if (true === $this->hasHeader) {
            /** @var array<int, string>|false|string $colName */
            $colName = $this->current();

            if (false !== $colName && !is_string($colName)) {
                $this->header = $colName;
            }
        }
    }

    public function hasHeader(): bool
    {
        return $this->hasHeader;
    }

    /**
     * Returns the array of column headers. This array is populated during the constructor if $hasHeader is true.
     *
     * @return array<int, string>
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * Allows you to modify or define the name of a column using its numerical index.
     * This method is useful for CSV files without a header row,
     * where you want to assign column names to process the data as an associative array.
     */
    public function mapIndexToColName(int $index, string $colName): static
    {
        $this->header[$index] = $colName;

        return $this;
    }

    /**
     * Converts an indexed array of data into an associative array using the column names defined in the $colsName property.
     * If an index does not have a corresponding column name, its value is omitted from the resulting associative array.
     *
     * @param array<int, string> $line
     *
     * @return array<int|string, ?string>
     */
    protected function mapLine(array $line): array
    {
        if (empty($this->header)) {
            return $line;
        }

        $buffer = [];
        foreach ($this->header as $index => $colName) {
            $buffer[$colName] = $line[$index] ?? null;
        }

        return $buffer;
    }

    /**
     * Description: Reads a specific line from the CSV file.
     * You can specify a line number with the $offset or read the current line if $offset is null.
     * It applies all defined sanitizers and filters before returning the line.
     *
     * @return array<int|string, ?string>|false false at EOF
     */
    public function lineAt(?int $offset = null): array|false
    {
        if (null !== $offset) {
            $this->seek($offset);
        }

        /** @var array<int, string>|false $line */
        $line = ($this->hasHeader) ? $this->fgetcsv(escape: '\\') : $this->current();
        if (false !== $line) {
            $line = ($this->hasHeader) ?
                array_combine($this->header, $line) :
                $this->mapLine($line)
            ;

            $filteredLine = $this->filter($line);

            if (null !== $filteredLine) {
                $this->sanitize($filteredLine);

                return $filteredLine;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Adds a callable function to the list of sanitizers. This function will be applied to every line read to clean or modify its data.
     *
     * @return $this
     */
    public function addSanitizer(callable $callable): self
    {
        $this->sanitizers[] = $callable;

        return $this;
    }

    /**
     * Applies all registered sanitizer functions to the given line. This method is used internally by lineAt and modifies the $line array in place.
     *
     * @param array<int|string, ?string> $line
     */
    protected function sanitize(array &$line): void
    {
        if (false === empty($this->sanitizers)) {
            foreach ($this->sanitizers as $sanitizer) {
                $sanitizer($line);
            }
        }
    }

    /**
     * Applies all registered filter functions to the given line.
     * If any of the filter functions returns false, the line is considered invalid, and the method returns null.
     *
     * @param array<int|string, ?string> $line
     *
     * @return array<int|string, ?string>|null
     */
    protected function filter(array $line): ?array
    {
        $isValid = true;

        if (false === empty($this->filters)) {
            foreach ($this->filters as $filter) {
                $isValid &= $filter($line);
            }
        }

        if ($isValid) {
            return $line;
        }

        return null;
    }

    /**
     * Adds a callable function to the list of filters.
     * This function will be applied to every line to determine if it should be included in the results.
     */
    public function addFilter(callable $callable): self
    {
        $this->filters[] = $callable;

        return $this;
    }

    protected function getRelativeOffset(int $offset): int
    {
        return ($this->hasHeader()) ? --$offset : $offset;
    }

    protected function validateInput(?int $from = null, ?int $to = null): void
    {
        if (null === $from && is_int($to)) {
            throw new \InvalidArgumentException('The $to parameter must be null when $from is null');
        }

        if (null === $to && is_int($from)) {
            throw new \InvalidArgumentException('The $from parameter must be null when $to is null');
        }

        if (is_int($from) && is_int($to) && $from > $to) {
            throw new \InvalidArgumentException('The $from parameter must be less than or equal to $to');
        }
    }

    /**
     * Provides a generator to iterate over the lines of the file.
     * It reads each line one by one, applying filters and sanitizers, and yields the valid lines.
     * This is the most memory-efficient way to read large files.
     */
    public function lines(?int $from = null, ?int $to = null): \Generator
    {
        $this->validateInput($from, $to);

        $offset = (null !== $from) ? $this->getRelativeOffset($from) : null;

        while ($this->valid()) {
            $line = $this->lineAt($offset);

            if (false !== $line) {
                yield $line;
            }

            if (null !== $offset) {
                ++$offset;

                if ($offset >= $to) {
                    break;
                }
            }
        }

        $this->rewind();
    }
}
