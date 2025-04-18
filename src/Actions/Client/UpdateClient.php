<?php

namespace FluxErp\Actions\Client;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Rulesets\Client\UpdateClientRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateClient extends FluxAction
{
    public static function models(): array
    {
        return [Client::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateClientRuleset::class;
    }

    public function performAction(): Model
    {
        $bankConnections = Arr::pull($this->data, 'bank_connections');
        $client = resolve_static(Client::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $client->fill($this->data);
        $client->save();

        if (! is_null($bankConnections)) {
            $client->bankConnections()->sync($bankConnections);
        }

        return $client->withoutRelations()->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['client_code'] .= ',' . ($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! resolve_static(Client::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
