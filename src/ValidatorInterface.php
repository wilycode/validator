<?php

declare(strict_types=1);

namespace WilyCode\Validator;

interface ValidatorInterface
{
    public function isValid(mixed $value): bool;

    /** @return list<Error> */
    public function getErrors(): array;

    public function getErrorBag(): ErrorBag;

    public function clearErrors(): static;

    public function isStop(): bool;

    public function setStop(bool $stop): static;
}
