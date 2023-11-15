<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateBankConnectionRequest;
use FluxErp\Models\BankConnection;
use FluxErp\Models\ContactBankConnection;
use Illuminate\Database\Eloquent\Model;

class UpdateBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateBankConnectionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): Model
    {
        $contactBankConnection = BankConnection::query()
            ->whereKey($this->data['id'])
            ->first();

        $contactBankConnection->fill($this->data);
        $contactBankConnection->save();

        return $contactBankConnection->withoutRelations()->fresh();
    }
}
