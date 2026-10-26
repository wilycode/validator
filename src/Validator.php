<?php

declare(strict_types=1);

namespace WilyCode\Validator;

abstract class Validator implements ValidatorInterface
{
    /** @var array<class-string, string> */
    private static array $codes = [];

    private ?ErrorBag $errorBag = null;
    private bool $stop = false;

    /** @param array<string, mixed> $options */
    protected function addError(string $name, array $options = []): void
    {
        $this->getErrorBag()->addError(new Error($this->resolveErrorCode($name), $options));
    }

    protected function resolveErrorCode(string $name): string
    {
        return sprintf(
            'validation.%s.%s',
            $this->getValidatorCode(),
            self::toSnakeCase($name),
        );
    }

    protected function getValidatorCode(): string
    {
        $class = static::class;
        if (isset(self::$codes[$class])) {
            return self::$codes[$class];
        }

        $reflection = new \ReflectionClass($this);
        if ($reflection->isAnonymous()) {
            return self::$codes[$class] = 'custom';
        }

        $shortName = $reflection->getShortName();
        $validatorName = preg_replace('/Validator$/', '', $shortName) ?? $shortName;

        return self::$codes[$class] = self::toSnakeCase($validatorName);
    }

    private static function toSnakeCase(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;

        return strtolower($value);
    }

    /** @return list<Error> */
    public function getErrors(): array
    {
        return $this->getErrorBag()->getErrors();
    }

    public function getErrorBag(): ErrorBag
    {
        return $this->errorBag ??= new ErrorBag();
    }

    /**
     * Validators are often reused across multiple validations.
     * To prevent error accumulation between calls, clear errors explicitly.
     */
    public function clearErrors(): static
    {
        $this->errorBag = new ErrorBag();

        return $this;
    }

    /**
     * A cloned validator must not retain the previous validation result.
     * Subclasses with mutable nested validators should clone those as well.
     */
    public function __clone(): void
    {
        $this->clearErrors();
    }

    abstract public function isValid(mixed $value): bool;

    public function isStop(): bool
    {
        return $this->stop;
    }

    public function setStop(bool $stop): static
    {
        $this->stop = $stop;

        return $this;
    }
}
