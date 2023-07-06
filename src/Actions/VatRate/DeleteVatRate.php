<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\VatRate;
use Illuminate\Support\Facades\Validator;

class DeleteVatRate implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:vat_rates,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'vat-rate.delete';
    }

    public static function description(): string|null
    {
        return 'delete vat rate';
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function execute()
    {
        return VatRate::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
