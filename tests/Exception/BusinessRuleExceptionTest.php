<?php

namespace Tourze\EasyAdminExtraBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EasyAdminExtraBundle\Exception\BusinessRuleException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(BusinessRuleException::class)]
final class BusinessRuleExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionInheritance(): void
    {
        $exception = new BusinessRuleException();

        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Business rule violation';
        $exception = new BusinessRuleException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Business rule violation';
        $code = 1001;
        $exception = new BusinessRuleException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \InvalidArgumentException('Previous exception');
        $exception = new BusinessRuleException('Business rule violation', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
