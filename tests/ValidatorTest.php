<?php

declare(strict_types=1);

namespace WilyCode\Validator\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Validator\Validator;

final class ValidatorTest extends TestCase
{
    public function testCustomValidatorUsesStructuredErrorsAndCanBeReused(): void
    {
        $validator = new class extends Validator {
            public function isValid(mixed $value): bool
            {
                if ($value !== 'ok') {
                    $this->addError('invalidValue', ['expected' => 'ok']);
                    return false;
                }

                return true;
            }
        };

        self::assertFalse($validator->isValid('no'));
        self::assertSame('validation.custom.invalid_value', $validator->getErrors()[0]->getCode());
        self::assertSame(['expected' => 'ok'], $validator->getErrors()[0]->getOptions());

        $validator->clearErrors();
        self::assertTrue($validator->isValid('ok'));
        self::assertSame([], $validator->getErrors());
    }

    public function testClonedValidatorDoesNotCarryPreviousErrors(): void
    {
        $original = new class extends Validator {
            public function isValid(mixed $value): bool
            {
                if ($value !== 'ok') {
                    $this->addError('invalid');
                    return false;
                }

                return true;
            }
        };

        self::assertFalse($original->isValid('bad'));
        $cloned = clone $original;
        self::assertCount(1, $original->getErrors());
        self::assertSame([], $cloned->getErrors());
        self::assertTrue($cloned->isValid('ok'));
        self::assertSame([], $cloned->getErrors());
    }

    public function testStopFlagBelongsToTheContractCore(): void
    {
        $validator = new class extends Validator {
            public function isValid(mixed $value): bool
            {
                return true;
            }
        };

        self::assertFalse($validator->isStop());
        self::assertSame($validator, $validator->setStop(true));
        self::assertTrue($validator->isStop());
    }
}
