<?php

declare(strict_types=1);

namespace WilyCode\Validator;

/**
 * Recursive validation error container.
 *
 * - errors: errors assigned directly to the current node/field
 * - items: child error bags, e.g. collection rows or multiple field items
 */
final class ErrorBag
{
    /** @var list<Error> */
    private array $errors = [];

    /** @var array<int|string, self> */
    private array $items = [];

    public function addError(Error $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    /** @param iterable<Error> $errors */
    public function addErrors(iterable $errors): self
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }

        return $this;
    }

    public function merge(self $bag): self
    {
        if ($bag === $this) {
            throw new \InvalidArgumentException('Cannot merge an ErrorBag into itself.');
        }

        $this->addErrors($bag->getErrors());

        foreach ($bag->getItems() as $key => $item) {
            $this->getOrCreateItem($key)->merge($item);
        }

        return $this;
    }

    public function getOrCreateItem(int|string $key): self
    {
        return $this->items[$key] ??= new self();
    }

    public function setItem(int|string $key, self $bag): self
    {
        if ($bag->contains($this)) {
            throw new \InvalidArgumentException('An ErrorBag cannot contain itself.');
        }

        $this->items[$key] = $bag;

        return $this;
    }

    private function contains(self $bag): bool
    {
        if ($bag === $this) {
            return true;
        }

        foreach ($this->items as $item) {
            if ($item->contains($bag)) {
                return true;
            }
        }

        return false;
    }

    public function hasItem(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function getItem(int|string $key): ?self
    {
        return $this->items[$key] ?? null;
    }

    /** @return list<Error> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /** @return array<int|string, self> */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Return a collision-free error tree.
     *
     * @return array{errors: list<Error>, items: array<int|string, mixed>}
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $key => $item) {
            $items[$key] = $item->toArray();
        }

        return ['errors' => $this->errors, 'items' => $items];
    }

    public function hasErrors(): bool
    {
        if ($this->errors !== []) {
            return true;
        }

        foreach ($this->items as $item) {
            if ($item->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return !$this->hasErrors();
    }
}
