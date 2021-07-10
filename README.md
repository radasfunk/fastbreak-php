# Very short description of the package

PHP driver for Fast Break.


## Installation

You can install the package via composer:

```bash
composer require radasfunk/fastbreak-php
```

## Usage

```php
$fastbreak = new Client(
    {{channelID}},
    {{apiKey}}'),
);

$res = $fastbreak->get('customers', [
    'filter[name]' => 'John',
]);
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Credits

-   [Alexander Lingris](https://github.com/radasfunk)
