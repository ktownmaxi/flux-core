<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use Livewire\Attributes\Locked;

class CommentForm extends FluxForm
{
    public ?string $comment = null;

    #[Locked]
    public ?int $id = null;

    public ?bool $is_internal = null;

    public ?bool $is_sticky = null;

    #[Locked]
    public ?int $model_id = null;

    #[Locked]
    public ?string $model_type = null;

    #[Locked]
    public ?int $parent_id = null;

    public function getActions(): array
    {
        return [
            'create' => CreateComment::class,
            'update' => UpdateComment::class,
            'delete' => DeleteComment::class,
        ];
    }
}
