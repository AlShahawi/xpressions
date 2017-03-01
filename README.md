Xpressions
====
A fluent PHP API for regular expressions.

## Installation
Xpressions now is not ready for production yet, but you can install it using composer via:
```
composer require alshahawi/xpressions:dev-master
```

## Examples
### Creating an instance
```php
$matcher = Xpressions::match();
```
### Matchting a string
```php
$matcher = Xpressions::match()->exact('foo');

var_dump($matcher->test('bar')); // -> false
var_dump($matcher->test('foo')); // -> true
```
### Inline Matching
In some situations (like validation) you might want to match something in the start of the line and before the end of that line, in that case you might use `begin()` and `end()` methods. For example:

```php
$matcher = Xpressions::match()->begin()->exact('bar')->end();

var_dump($matcher->test('foo bar')); // -> false
var_dump($matcher->test('bar foo')); // -> false
var_dump($matcher->test('bar'));     // -> true
```
> NOTE: without `begin()` and `end()` all of the above tests will match (returning true), because whatever the matched in the start or the end of the line will return true unless you specified the begin and the end of the line (the whole text).

### Matching an optional string
```php
$matcher = Xpressions::match()->exact('foo')->maybe('bar')->exact('baz');

var_dump($matcher->test('foo'));       // -> false
var_dump($matcher->test('foobar'));    // -> false
var_dump($matcher->test('foobarbaz')); // -> true
var_dump($matcher->test('foobaz'));    // -> true
```

### Matching alphanumeric & underscores
```php
$matcher = Xpressions::match()->word();

var_dump($matcher->test('!')); // -> false
var_dump($matcher->test('a')); // -> true
var_dump($matcher->test('1')); // -> true
var_dump($matcher->test('_')); // -> true
```
> You may use `words()` to match a one or more word characters.

> You may use `nonWord()` as an inverse to this method.

### Matching a digit
```php
$matcher = Xpressions::match()->digit();

var_dump($matcher->test('!')); // -> false
var_dump($matcher->test('a')); // -> false
var_dump($matcher->test('1')); // -> true
```
> You may use `digits()` to match a one or more word characters.

> You may use `nonDigit()` as an inverse to this method.

### Matching any of a given values
```php
$matcher = Xpressions::match()->any('foo', 'bar', 'baz');

var_dump($matcher->test('something')); // -> false
var_dump($matcher->test('foo'));       // -> true
var_dump($matcher->test('bar'));       // -> true
var_dump($matcher->test('baz'));       // -> true
```
## Advanced Examples

### Matching an email address
```php
$matcher = Xpressions::match()
    ->begin() // match a line start
    ->oneOrMore(function($xpr) {
        $xpr->word()->or('.');
    })
    ->exact('@')
    ->words()
    ->oneOrMore(function($xpr) {
        $xpr->maybe('.')
            ->word();
    })
    ->end(); // match a line end

var_dump($matcher->test('invalid'));        // ->false
var_dump($matcher->test('me@example.com')); // ->true
```

## Interested in more advanced examples?
Many of examples will be written and documented soon!, but you can view `tests/XpressionsTest.php` for now.
