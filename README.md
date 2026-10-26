# WilyCode Validator

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/wilycode/validator/actions/workflows/tests.yml/badge.svg)](https://github.com/wilycode/validator/actions/workflows/tests.yml)

A lightweight, framework-independent validation contract for PHP 8.4+. Define reusable validators, report structured validation errors and integrate validation into your own forms or applications. **No built-in validation rules** are included in this core package.

> **WilyCode PHP Ecosystem**
>
> Part of **[WilyCode](https://github.com/wilycode/wilycode)** — independent, reusable PHP components with separate repositories, releases and Composer installations.
>
> **[Explore the WilyCode PHP Ecosystem →](https://github.com/wilycode/wilycode)**

## Contents

- [Features](#features)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Structured and nested errors](#structured-and-nested-errors)
- [API reference](#api-reference)
- [Validation state and cloning](#validation-state-and-cloning)
- [Related packages](#related-packages)
- [Development](#development)
- [License](#license)

## Features

- A small `ValidatorInterface` contract and extensible `Validator` base class.
- Structured `Error` objects with machine-readable codes and contextual options.
- Recursive `ErrorBag` for field, item and collection errors.
- Error reset and safe cloning of the base validator state.
- No runtime dependencies beyond PHP 8.4+.

## Installation

```bash
composer require wilycode/validator
```

Requires **PHP 8.4+**. Install `wilycode/validators` separately for ready-made validation rules.

## Quick start

Extend `Validator` and implement `isValid(mixed $value): bool`:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use WilyCode\Validator\Validator;

final class EvenNumberValidator extends Validator
{
    public function isValid(mixed $value): bool
    {
        // Validators may be reused for different values.
        $this->clearErrors();

        if (!is_int($value) || $value % 2 !== 0) {
            $this->addError('invalid', ['expected' => 'even integer']);

            return false;
        }

        return true;
    }
}

$validator = new EvenNumberValidator();
$validator->isValid(3); // false
$validator->getErrors()[0]->getCode(); // 'validation.even_number.invalid'
$validator->getErrors()[0]->getOptions(); // ['expected' => 'even integer']
$validator->isValid(4); // true; previous errors cleared
```

You can also implement `ValidatorInterface` directly when a custom base class is preferred. Unlike the base class, the interface does not define how errors are stored.

## Structured and nested errors

`getErrors()` returns a list of `Error` objects **directly attached** to the current validator. Each error exposes `getCode(): string` and `getOptions(): array`.

`getErrorBag()` provides nested error storage, useful for fields, form groups and collection rows:

```php
use WilyCode\Validator\Error;
use WilyCode\Validator\ErrorBag;

$bag = new ErrorBag();
$bag->addError(new Error('validation.required'));
$bag->getOrCreateItem(0)->addError(new Error('validation.invalid'));

$tree = $bag->toArray();
// [
//     'errors' => [Error('validation.required')],
//     'items' => [0 => ['errors' => [Error('validation.invalid')], 'items' => []]],
// ]
```

Each node uses distinct `errors` and `items` keys, so child names cannot overwrite direct errors. `toArray()` preserves `Error` **objects**, not translated messages. Translate codes in your application. `hasErrors()` and `isEmpty()` consider nested items as well.

Use `addErrors()` or `merge()` to combine multiple error sources. `setItem()` accepts a nested error bag; cycles are rejected to prevent infinite recursion. Merging a bag into itself is also rejected.

## API reference

| API | Purpose |
| --- | --- |
| `ValidatorInterface::isValid(mixed $value): bool` | Validate a value; implemented by the concrete validator. |
| `$validator->getErrors(): array` | Return direct `Error` objects for the validator. |
| `$validator->getErrorBag(): ErrorBag` | Access its structured error container. |
| `$validator->clearErrors(): static` | Clear previous errors, including nested items. |
| `$validator->setStop(bool $stop): static` | Set a flag that composite validators may use to stop after a failure. |
| `$validator->isStop(): bool` | Read the stop flag. |
| `$bag->addError(Error $error): self` | Add a direct error. |
| `$bag->addErrors(iterable $errors): self` | Add several direct errors. |
| `$bag->getOrCreateItem(int\|string $key): ErrorBag` | Get/create a child bag. |
| `$bag->setItem(int\|string $key, ErrorBag $bag): self` | Attach a child bag, rejecting cycles. |
| `$bag->merge(ErrorBag $bag): self` | Merge direct and nested errors. |
| `$bag->toArray(): array` | Return a tree containing `errors` and `items`. |
| `$bag->hasErrors(): bool` | Detect direct or nested errors. |
| `$bag->isEmpty(): bool` | Check for no direct or nested errors. |

## Validation state and cloning

The base `Validator` stores errors until `clearErrors()` is called. **Call `clearErrors()` at the start of `isValid()`** in custom validators that can be used more than once. The ready-made validators in `wilycode/validators` and WilyCode Form follow this convention.

Cloning a `Validator` resets its error bag and preserves configuration such as the stop flag. If a composite validator has nested mutable validators, override `__clone()` and clone those children as well; you may call `parent::__clone()` to reset inherited validation state.

The `stop` flag is **a signal**, not an automatic early return: composite validation code decides whether to use it.

## Related packages

- [`wilycode/validators`](https://github.com/wilycode/validators) — ready-made e-mail, date, length, slug and other validators.
- [`wilycode/form`](https://github.com/wilycode/form) — fields, fieldsets and collections that use validators and filters.
- [`wilycode/recaptcha-validator`](https://github.com/wilycode/recaptcha-validator) — server-side Google reCAPTCHA verification.

## Development

```bash
composer install
composer validate --strict
composer test
```

GitHub Actions runs validation and tests on **PHP 8.4 and 8.5**. Unit tests are included in the source repository and excluded from standard Composer distribution archives.

## License

MIT © 2026 Simon Piter. See [LICENSE](LICENSE).
