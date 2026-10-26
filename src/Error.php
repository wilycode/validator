<?php

declare(strict_types=1);

namespace WilyCode\Validator;

final class Error
{
    private string $code;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $code, array $options = [])
    {
        $this->code = $code;
        $this->options = $options;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
