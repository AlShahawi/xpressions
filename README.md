Xpressions
====
A fluent PHP API for regular expressions.

## Installation
Xpressions now is not ready for production yet, but you can install it using composer via:
```
composer require alshahawi/xpressions:dev-master
```

## Examples
### Creating An Instance
```php
$matcher = Xpressions::match();
```
### Matchting a string
```php
$matcher = Xpressions::match()->exact('foo');

var_dump($matcher->test('bar')); // -> false
var_dump($matcher->test('foo')); // -> true
```

### Matching an optional string
```php
$matcher = Xpressions::match()->exact('foo')->maybe('bar')->exact('baz');

var_dump($matcher->test('foo')); // -> false
var_dump($matcher->test('foobar')); // -> false
var_dump($matcher->test('foobarbaz')); // -> true
var_dump($matcher->test('foobaz')); // -> true
```

### Matching alphanumeric & underscores
```php
$matcher = Xpressions::match()->word();

var_dump($matcher->test('!@#$%')); // -> false
var_dump($matcher->test('foo')); // -> true
var_dump($matcher->test('123456')); // -> true
var_dump($matcher->test('mixed123')); // -> true
```
> You can use `nonWord()` as an inverse to this method.

### Matching a digit
Matching one digit:
```php
$matcher = Xpressions::match()->begin()->digit()->end();

var_dump($matcher->test('foo')); // -> false
var_dump($matcher->test('bar')); // -> false
var_dump($matcher->test('123456')); // -> false
var_dump($matcher->test('1')); // -> true
```
Matching one or more digits:
```php
$matcher = Xpressions::match()->digit()->oneOrMore();

var_dump($matcher->test('foo')); // -> false
var_dump($matcher->test('bar')); // -> false
var_dump($matcher->test('123456')); // -> true
var_dump($matcher->test('1')); // -> true
```

> You can use `nonDigit()` as an inverse to this method.

### Matching an email address
```php
$matcher = Xpressions::match()
    ->begin() // match a line start
    ->oneOrMore(function($xpr) {
        $xpr->word()->or('.');
    })
    ->exact('@')
    ->oneOrMore(function($xpr) {
        $xpr->maybe('.')
            ->word();
    })
    ->end(); // match a line end

var_dump($matcher->test('invalid')); // ->false
var_dump($matcher->test('me@example.com')); // ->true
```

## Interested in more advanced examples?
Many of examples will be written and documented soon!, but you can view `tests/XpressionsTest.php` for now.
