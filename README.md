# PHP CSV Reader
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/inwebo/csv-reader/.github%2Fworkflows%2Flibrary.yml?branch=master&style=flat-square)
![Packagist Version](https://img.shields.io/packagist/v/inwebo/csv-reader?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dd/inwebo/csv-reader?style=flat-square)

This PHP class, `Inwebo\Csv\Reader`, provides a simple yet powerful way to read and process CSV files. Built as an extension of PHP's `SplFileObject`, it offers advanced features like **column name mapping**, **data filtering**, and **sanitization** to streamline your CSV processing tasks.

-----

### Key Features

* **Column Name Mapping**: Automatically maps each line's data to an associative array using the CSV header as keys, making your code more readable and maintainable.
* **Data Sanitization**: Apply one or more callable functions to each line to clean and format the data before it's used.
* **Data Filtering**: Use callable functions to validate and filter out rows that don't meet your criteria.
* **Generator-based Iteration**: Process large files efficiently using a `Generator` to iterate over lines without consuming too much memory.
* **Inherits `SplFileObject`**: Leverage all the native features and performance benefits of `SplFileObject` for file handling.

-----

### Installation

```shell
  composer req inwebo/csv-reader
```
## Tests

```shell
  composer phpunit
```

## PhpStan

```shell
  composer phpstan
```
> Level 10
-----

### Usage

#### Basic Reading

To get started, simply instantiate the `Reader` class with the path to your CSV file. By default, it assumes the first row contains column names.

```php
use Inwebo\Csv\Reader;

$csvFile = new Reader('path/to/your/file.csv');

foreach ($csvFile->lines() as $line) {
    // $line will be an associative array, e.g., ['column_name' => 'value']
    print_r($line);
}
```

#### Disabling Column Names

If your CSV file does not have a header row, you can disable the column name mapping by setting the `hasColName` parameter to `false`.

```php
use Inwebo\Csv\Reader;

$csvFile = new Reader('path/to/your/file.csv', hasColName: false);

foreach ($csvFile->lines() as $line) {
    // $line will be a numeric array, e.g., ['value1', 'value2']
    print_r($line);
}
```

-----

### Advanced Usage: Sanitizers and Filters

You can add multiple sanitizers and filters to your `Reader` instance. They are executed sequentially in the order they are added.

#### Sanitizers

Sanitizers are used to modify the data. The callback receives the line array by reference, allowing you to directly alter its values.

```php
use Inwebo\Csv\Reader;

$csvFile = new Reader('path/to/your/file.csv');

// Add a sanitizer to trim whitespace from all values
$csvFile->addSanitizer(function (array &$line) {
    $line = array_map('trim', $line);
});

// Add another sanitizer to convert a specific column to an integer
$csvFile->addSanitizer(function (array &$line) {
    if (isset($line['age'])) {
        $line['age'] = (int) $line['age'];
    }
});
```

#### Filters

Filters are used to validate and exclude entire rows. If a filter returns `false`, the line will be skipped and will not be yielded by the generator.

```php
use Inwebo\Csv\Reader;

$csvFile = new Reader('path/to/your/file.csv');

// Add a filter to only include rows where the 'status' column is 'active'
$csvFile->addFilter(function (array $line) {
    return isset($line['status']) && $line['status'] === 'active';
});

// Add another filter to only include users older than 25
$csvFile->addFilter(function (array $line) {
    return isset($line['age']) && (int) $line['age'] > 25;
});
```

With both sanitizers and filters in place, the processing loop becomes a clean, declarative statement of what you want to achieve.

```php
foreach ($csvFile->lines() as $validAndSanitizedLine) {
    // This line has passed all your checks and is ready to be used
    print_r($validAndSanitizedLine);
}
```