<?php

declare(strict_types=1);

namespace WilyCode\Validator\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Validator\Error;
use WilyCode\Validator\ErrorBag;

final class ErrorBagTest extends TestCase
{
    public function testDirectAndIndexedErrorsRemainSeparate(): void
    {
        $bag = new ErrorBag();
        $error = new Error('validation.required');
        $child = new Error('validation.invalid');
        $bag->addError($error);
        $bag->getOrCreateItem(0)->addError($child);

        $tree = $bag->toArray();
        self::assertSame([$error], $tree['errors']);
        self::assertSame([$child], $tree['items'][0]['errors']);
        self::assertSame([], $tree['items'][0]['items']);
    }

    public function testEmptyErrorBagReturnsStableShape(): void
    {
        self::assertSame(['errors' => [], 'items' => []], (new ErrorBag())->toArray());
    }

    public function testMergePreservesNestedErrorStructure(): void
    {
        $destination = new ErrorBag();
        $destination->getOrCreateItem('items')->addError(new Error('first'));
        $source = new ErrorBag();
        $source->addError(new Error('top'));
        $source->getOrCreateItem('items')->addError(new Error('second'));

        self::assertSame($destination, $destination->merge($source));
        self::assertSame(['top'], array_map(static fn (Error $error): string => $error->getCode(), $destination->getErrors()));
        self::assertSame(['first', 'second'], array_map(
            static fn (Error $error): string => $error->getCode(),
            $destination->getItem('items')->getErrors(),
        ));
        self::assertTrue($destination->hasErrors());
        self::assertFalse($destination->isEmpty());
        self::assertSame(['second'], array_map(
            static fn (Error $error): string => $error->getCode(),
            $source->getItem('items')->getErrors(),
        ));
    }

    public function testCannotMergeErrorBagIntoItself(): void
    {
        $bag = new ErrorBag();
        $bag->getOrCreateItem('item')->addError(new Error('invalid'));

        $this->expectException(\InvalidArgumentException::class);
        $bag->merge($bag);
    }

    public function testCannotCreateDirectCycle(): void
    {
        $bag = new ErrorBag();

        $this->expectException(\InvalidArgumentException::class);
        $bag->setItem('self', $bag);
    }

    public function testCannotCreateIndirectCycle(): void
    {
        $parent = new ErrorBag();
        $child = new ErrorBag();
        $parent->setItem('child', $child);

        $this->expectException(\InvalidArgumentException::class);
        $child->setItem('parent', $parent);
    }
}
