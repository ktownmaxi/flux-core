<?php

namespace FluxErp\Livewire\Mail;

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Mail extends CommunicationList
{
    public ?string $folderId = null;

    public array $folders = [];

    #[Locked]
    public array $mailAccounts = [];

    public CommunicationForm $mailMessage;

    public array $selectedFolderIds = [];

    protected string $view = 'flux::livewire.mail.mail';

    public function mount(): void
    {
        parent::mount();
        $this->mailAccounts = auth()
            ->user()
            ->mailAccounts()
            ->get(['mail_accounts.id', 'uuid', 'email'])
            ?->toArray() ?? [];
        $this->loadFolders();

        $this->folders[] = [
            'id' => 'all',
            'name' => __('All Messages'),
            'children' => [],
        ];
    }

    public function download(Media $mediaItem): false|BinaryFileResponse
    {
        if (! file_exists($mediaItem->getPath())) {
            if (method_exists($this, 'notification')) {
                $this->notification()->error(__('File not found!'))->send();
            }

            return false;
        }

        return response()->download($mediaItem->getPath(), $mediaItem->file_name);
    }

    public function getNewMessages(): void
    {
        $mailAccounts = resolve_static(MailAccount::class, 'query')
            ->whereIntegerInRaw('id', array_column($this->mailAccounts, 'id'))
            ->get();

        foreach ($mailAccounts as $mailAccount) {
            SyncMailAccountJob::dispatch($mailAccount);
        }
    }

    #[Renderless]
    public function showMail(Communication $message): void
    {
        $this->skipRender();
        $this->mailMessage->fill($message);
        $this->mailMessage->text_body = nl2br($this->mailMessage->text_body);

        $this->js(<<<'JS'
            writeHtml();
            $modalOpen('show-mail');
        JS);
    }

    public function updatedFolderId(): void
    {
        if (is_null($this->folderId) || $this->folderId === 'all') {
            $this->selectedFolderIds = [];
        } elseif (! is_numeric($this->folderId)) {
            $folderTree = data_get(Arr::keyBy($this->folders, 'id'), $this->folderId . '.children', []);
            $this->selectedFolderIds = array_column(to_flat_tree($folderTree), 'id');
        } else {
            $folderTree = $this->findFolderIdById($this->folders, $this->folderId)['children'] ?? [];
            $this->selectedFolderIds = array_merge(
                [$this->folderId],
                array_column(to_flat_tree($folderTree), 'id')
            );
        }

        $this->search = '';

        $this->applyUserFilters();
    }

    protected function findFolderIdById($folders, $id): ?array
    {
        foreach ($folders as $element) {
            if (($element['id'] ?? false) && $element['id'] == $id) {
                return $element;
            }

            if (($element['children'] ?? false) && is_array($element['children'])) {
                $found = $this->findFolderIdById($element['children'], $id);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->where('communication_type_enum', 'mail')
            ->whereIntegerInRaw('mail_account_id', array_column($this->mailAccounts, 'id'))
            ->when($this->folderId, function (Builder $builder): void {
                $builder->whereIntegerInRaw('mail_folder_id', $this->selectedFolderIds);
            });
    }

    protected function loadFolders(): void
    {
        foreach ($this->mailAccounts as $mailAccount) {
            $this->folders[data_get($mailAccount, 'id')] = [
                'id' => data_get($mailAccount, 'uuid'),
                'name' => resolve_static(MailAccount::class, 'query')
                    ->whereKey(data_get($mailAccount, 'id'))
                    ->value('email'),
                'children' => resolve_static(MailFolder::class, 'familyTree')
                    ->where('parent_id', null)
                    ->where('mail_account_id', data_get($mailAccount, 'id'))
                    ->get()
                    ->toArray(),
            ];
        }
    }
}
