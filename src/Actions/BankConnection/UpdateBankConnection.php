<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateBankConnectionRequest;
use FluxErp\Models\BankConnection;
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
        return [BankConnection::class];
    }

    public function performAction(): Model
    {
        $bankConnection = BankConnection::query()
            ->whereKey($this->data['id'])
            ->first();

        $bankConnection->fill($this->data);
        $bankConnection->save();

        return $bankConnection->withoutRelations()->fresh();
    }
}
