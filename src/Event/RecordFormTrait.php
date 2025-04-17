<?php

namespace Tourze\EasyAdminExtraBundle\Event;

trait RecordFormTrait
{
    private object $model;

    private array $form;

    public function getModel(): object
    {
        return $this->model;
    }

    public function setModel(object $model): void
    {
        $this->model = $model;
    }

    public function getForm(): array
    {
        return $this->form;
    }

    public function setForm(array $form): void
    {
        $this->form = $form;
    }
}
