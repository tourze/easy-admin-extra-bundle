<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\FilterService;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;

class FilterServiceTest extends TestCase
{
    private FilterService $filterService;
    private ChoiceService $choiceService;
    private EntityDescriber $entityDescriber;

    protected function setUp(): void
    {
        $this->choiceService = $this->createMock(ChoiceService::class);
        $this->entityDescriber = $this->createMock(EntityDescriber::class);
        $this->filterService = new FilterService($this->choiceService, $this->entityDescriber);
    }

    public function testCreateFilterFromPropertyReturnsNullWhenNoFilterableAttribute(): void
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $property->expects(self::once())
            ->method('getAttributes')
            ->willReturn([]);

        $result = $this->filterService->createFilterFomProperty($property);

        self::assertNull($result);
    }
}