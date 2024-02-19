<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_silently(CreateAddressRequest::class)->rules();
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): Address
    {
        $tags = Arr::pull($this->data, 'tags');

        if (! data_get($this->data, 'is_main_address', false)
            && ! app(Address::class)->query()
                ->where('contact_id', $this->data['contact_id'])
                ->where('is_main_address', true)
                ->exists()
        ) {
            $this->data['is_main_address'] = true;
        }

        if (! data_get($this->data, 'is_invoice_address', false)
            && ! app(Address::class)->query()
                ->where('contact_id', $this->data['contact_id'])
                ->where('is_invoice_address', true)
                ->exists()
        ) {
            $this->data['is_invoice_address'] = true;
        }

        if (! data_get($this->data, 'is_delivery_address', false)
            && ! app(Address::class)->query()
                ->where('contact_id', $this->data['contact_id'])
                ->where('is_delivery_address', true)
                ->exists()
        ) {
            $this->data['is_delivery_address'] = true;
        }

        $contactOptions = Arr::pull($this->data, 'contact_options', []);

        $address = app(Address::class, ['attributes' => $this->data]);
        $address->save();

        if ($tags) {
            $address->attachTags(app(Tag::class)->query()->whereIntegerInRaw('id', $tags)->get());
        }

        if (app(CreateContactOption::class)->canPerformAction(false)) {
            foreach ($contactOptions as $contactOption) {
                $contactOption['address_id'] = $address->id;
                try {
                    CreateContactOption::make($contactOption)->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }

        if ($this->data['address_types'] ?? false) {
            $addressTypes = app(AddressType::class)->query()
                ->whereIntegerInRaw('id', $this->data['address_types'])
                ->where('is_unique', true)
                ->whereHas(
                    'addresses',
                    fn (Builder $query) => $query->where('contact_id', $this->data['contact_id'])
                )
                ->get();

            foreach ($addressTypes as $addressType) {
                $addressType->addresses()->detach($address->contact->addresses->pluck('id')->toArray());
            }

            foreach ($this->data['address_types'] as $addressType) {
                $address->addressTypes()->attach($addressType);
            }
        }

        return $address->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Address::class));

        $this->data = $validator->validate();
    }
}
