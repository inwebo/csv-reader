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
    private array $colsName = [];

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
     * @param ?resource $context
     */
    public function __construct(
        string $filename,
        string $mode = 'r',
        bool $useIncludePath = false,
        mixed $context = null,
        private readonly bool $hasColName = true,
    ) {
        parent::__construct($filename, $mode, $useIncludePath, $context);
        $this->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD);

        if (true === $this->hasColName) {
            /** @var array<int, string>|false|string $colName */
            $colName = $this->current();

            if (false !== $colName && !is_string($colName)) {
                $this->colsName = $colName;
            }
        }
    }

    /**
     * Returns the array of column headers. This array is populated during the constructor if $hasColName is true.
     *
     * @return array<int, string>
     */
    public function getColsName(): array
    {
        return $this->colsName;
    }

    /**
     * Allows you to modify or define the name of a column using its numerical index.
     * This method is useful for CSV files without a header row,
     * where you want to assign column names to process the data as an associative array.
     */
    public function mapIndexToColName(int $index, string $colName): static
    {
        $this->colsName[$index] = $colName;

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
        if (empty($this->colsName)) {
            return $line;
        }

        $buffer = [];
        foreach ($this->colsName as $index => $colName) {
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
        $line = ($this->hasColName) ? $this->fgetcsv(escape: '\\') : $this->current();
        if (false !== $line) {
            $line = ($this->hasColName) ?
                array_combine($this->colsName, $line) :
                $this->mapLine($line)
            ;

            $validatedLine = $this->filter($line);

            if (null !== $validatedLine) {
                $this->sanitize($validatedLine);

                return $validatedLine;
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

    /**
     * Provides a generator to iterate over the lines of the file.
     * It reads each line one by one, applying filters and sanitizers, and yields the valid lines.
     * This is the most memory-efficient way to read large files.
     */
    public function lines(): \Generator
    {
        while ($this->valid()) {
            $line = $this->lineAt();

            if (false !== $line) {
                yield $line;
            }
        }

        $this->rewind();
    }
}
