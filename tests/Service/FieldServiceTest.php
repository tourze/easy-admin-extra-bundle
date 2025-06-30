<?php

declare(strict_types=1);

namespace Tourze\EasyAdminExtraBundle\Tests\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminExtraBundle\Service\FieldService;
use Tourze\EasyAdminExtraBundle\Service\ListColumnService;
use Tourze\EasyAdminExtraBundle\Service\FormFieldService;
use Tourze\EasyAdminExtraBundle\Service\ChoiceService;
use Tourze\EasyAdminExtraBundle\Service\EntityDescriber;
use Tourze\EcolBundle\Service\Engine;

class FieldServiceTest extends TestCase
{
    private FieldService $fieldService;
    private Engine $engine;
    private ListColumnService $listColumnService;
    private FormFieldService $formFieldService;
    private ChoiceService $choiceService;
    private EntityDescriber $entityDescriber;

    protected function setUp(): void
    {
        $this->engine = $this->createMock(Engine::class);
        $this->listColumnService = $this->createMock(ListColumnService::class);
        $this->formFieldService = $this->createMock(FormFieldService::class);
        $this->choiceService = $this->createMock(ChoiceService::class);
        $this->entityDescriber = $this->createMock(EntityDescriber::class);
        
        $this->fieldService = new FieldService(
            $this->engine,
            $this->listColumnService,
            $this->formFieldService,
            $this->choiceService,
            $this->entityDescriber
        );
    }

    public function testCreateFieldFromPropertyReturnsNullWhenNoType(): void
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $property->expects(self::once())
            ->method('getType')
            ->willReturn(null);

        $result = $this->fieldService->createFieldFromProperty($property, 'index');

        self::assertNull($result);
    }

    public function testCreateFieldFromPropertyReturnsNullWithoutListOrFormAttributes(): void
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $type = $this->createMock(\ReflectionNamedType::class);
        
        $property->expects(self::once())
            ->method('getType')
            ->willReturn($type);
            
        // 没有 ListColumn 或 FormField 属性时返回空数组
        $property->expects(self::any())
            ->method('getAttributes')
            ->willReturn([]);

        $result = $this->fieldService->createFieldFromProperty($property, 'index');

        // 没有 ListColumn 或 FormField 时应该返回 null
        self::assertNull($result);
    }
}