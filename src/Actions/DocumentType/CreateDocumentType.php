<?php

namespace FluxErp\Actions\DocumentType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use Illuminate\Support\Facades\Validator;

/**
 * @deprecated
 */
class CreateDocumentType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = app(CreateDocumentTypeRequest::class)->rules();
    }

    public static function models(): array
    {
        return [DocumentType::class];
    }

    public function performAction(): DocumentType
    {
        $documentType = app(DocumentType::class, ['attributes' => $this->data]);
        $documentType->save();

        return $documentType->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(DocumentType::class));

        $this->data = $validator->validate();
    }
}
