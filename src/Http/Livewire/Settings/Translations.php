<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\Language;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Spatie\TranslationLoader\LanguageLine;

class Translations extends Component
{
    public array $translations;

    public array $locales;

    public string $locale;

    public int $index = -1;

    public bool $showTranslationModal = false;

    public string $search = '';

    protected $listeners = [
        'closeModal',
    ];

    public function boot(): void
    {
        $this->translations = LanguageLine::all()->toArray();

        $this->locales = Language::all('language_code')
            ->pluck('language_code')
            ->toArray();

        $this->locale = app()->getLocale();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.translations');
    }

    public function show(int $index = null): void
    {
        $this->index = is_null($index) ? -1 : $index;

        if (! is_null($index)) {
            $this->emitTo('settings.translation-edit', 'show', $this->locale, $this->translations[$index]);
        } else {
            $this->emitTo('settings.translation-edit', 'show', $this->locale);
        }

        $this->showTranslationModal = true;
        $this->skipRender();
    }

    public function closeModal(array $translation, bool $delete = false): void
    {
        $key = array_search($translation['id'], array_column($this->translations, 'id'));

        if (! $delete) {
            if ($key === false) {
                $this->translations[] = $translation;
            } else {
                $this->translations[$key] = $translation;
            }
        } elseif ($key !== false) {
            unset($this->translations[$key]);
        }

        $this->index = 0;
        $this->showTranslationModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->emitTo('settings.translation-edit', 'delete');
    }

    public function updatedLocale(): void
    {
        $this->skipRender();
    }

    public function updatedSearch(): void
    {
        if ($this->search) {
            $result = \FluxErp\Models\LanguageLine::search($this->search)->fastPaginate();

            $this->translations = count($result->items()) ? $result->items() : LanguageLine::all()->toArray();
        } else {
            $this->translations = LanguageLine::all()->toArray();
        }

        $this->skipRender();
    }
}
