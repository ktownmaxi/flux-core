<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;
use Illuminate\Database\Eloquent\Model;
use Livewire\Drawer\Utils;
use Livewire\Form as BaseForm;
use ReflectionProperty;

abstract class FluxForm extends BaseForm
{
    protected mixed $actionResult = null;

    protected bool $checkPermission = true;

    protected ?string $modelClass = null;

    abstract protected function getActions(): array;

    public function canAction(string $action): bool
    {
        $actionClass = data_get($this->getActions(), $action);

        if (! is_string($actionClass)) {
            return false;
        }

        return resolve_static($actionClass, 'canPerformAction', [false]);
    }

    public function create(): void
    {
        $response = $this->makeAction('create')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function delete(): void
    {
        $response = $this->makeAction('delete')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

        $this->reset();
    }

    public function getActionResult(): mixed
    {
        return $this->actionResult;
    }

    public function getModelInstance(): ?Model
    {
        if (is_null($this->modelClass)) {
            return null;
        }

        return app($this->modelClass)->query()
            ->where($this->getKey(), data_get($this, $this->getKey()))
            ->first();
    }

    public function save(): void
    {
        if ($this->{$this->getKey()}) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function setCheckPermission(bool $checkPermission): static
    {
        $this->checkPermission = $checkPermission;

        return $this;
    }

    public function toActionData(): array
    {
        return Utils::getPublicProperties(
            $this,
            fn (ReflectionProperty $property) => collect($property->getAttributes())
                ->doesntContain(fn ($attribute) => $attribute->getName() === ExcludeFromActionData::class)
        );
    }

    public function update(): void
    {
        $response = $this->makeAction('update')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate()
            ->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function validateDelete($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate(
            array_intersect_key(
                array_merge(
                    $this->makeAction('delete')->getRules(),
                    $rules ?? []
                ),
                $this->toActionData()
            ),
            $messages,
            $attributes
        );
    }

    public function validateSave($rules = null, $messages = [], $attributes = []): void
    {
        parent::validate(
            array_intersect_key(
                array_merge(
                    $this->makeAction($this->{$this->getKey()} ? 'update' : 'create')
                        ->setRulesFromRulesets()
                        ->getRules(),
                    $rules ?? []
                ),
                $this->toActionData()
            ),
            $messages,
            $attributes
        );
    }

    protected function getKey(): string
    {
        return 'id';
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        return $this->getActions()[$name]::make($data ?? $this->toActionData());
    }
}
