<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\CreateContactBankConnectionRuleset;

class CreateContactBankConnection extends FluxAction
{
    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateContactBankConnectionRuleset::class;
    }

    public function performAction(): ContactBankConnection
    {
        if ($this->getData('is_credit_account')) {
            $this->data['balance'] = 0;
        }

        $contactBankConnection = app(ContactBankConnection::class, ['attributes' => $this->data]);
        $contactBankConnection->save();

        return $contactBankConnection->refresh();
    }
}
