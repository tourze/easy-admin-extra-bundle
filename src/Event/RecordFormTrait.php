<?php

namespace Tourze\EasyAdminExtraBundle\Event;

trait RecordFormTrait
{
    private object $model;

    /**
     * @var array<string, mixed>
     */
    private array $form;

    public function getModel(): object
    {
        return $this->model;
    }

    public function setModel(object $model): void
    {
        $this->model = $model;
    }

    /**
     * @return array<string, mixed>
     */
    public function getForm(): array
    {
        return $this->form;
    }

    /**
     * @param array<string, mixed> $form
     */
    public function setForm(array $form): void
    {
        $this->form = $form;
    }
}
