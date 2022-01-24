# easy way to read and write csv files

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanomatteo/csv-read-write.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/csv-read-write)
[![Tests](https://github.com/ivanomatteo/csv-read-write/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/ivanomatteo/csv-read-write/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanomatteo/csv-read-write.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/csv-read-write)

This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.


## Installation

You can install the package via composer:

```bash
composer require ivanomatteo/csv-read-write
```

## Usage

```php
use IvanoMatteo\CsvReadWrite\CsvWriter;

(new CsvWriter(__DIR__."/tmp.csv"))
->format(';','"',"\\")
->write(
    [
        ['aaa','bbb', 'ccc'],
        ['foo','bar', 'baz'],
    ], 
    ['column1', 'column2', 'column3'] // optional first row
);


```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ivano Matteo](https://github.com/ivanomatteo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
