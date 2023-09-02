<?php

namespace FluxErp\Providers;

use FluxErp\View\Layouts\App;
use FluxErp\View\Layouts\Printing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /** use @extendFlux() at the end of the component, not the beginning */
        Blade::directive('extendFlux', function (string $expression) {
            $expression = trim($expression, '()');

            // Split the expression into its components
            $parts = explode(',', $expression);

            // Trim and remove quotes from each part
            $view = trim($parts[0], ' "\'');
            $viewPaths = $parts[1] ?? false ? eval('return ' . $parts[1] . ';') : null;

            if (is_string($viewPaths)) {
                $viewPaths = [$viewPaths];
            }

            $viewPaths = array_merge($viewPaths ?? [], [flux_path('resources/views')]);

            $finder = new FileViewFinder(app('files'), $viewPaths);
            $filePath = $finder->find($view);

            return Blade::compileString(file_get_contents($filePath));
        });

        Blade::component(App::class, 'layouts.app');
        Blade::component(Printing::class, 'layouts.print.index');
        Blade::component(Printing::class, 'layouts.print');
        config([
            'livewire.layout' => 'flux::layouts.app',
        ]);

        // Register Printing views as blade components
        $views[] = __DIR__ . '/../../resources/views/printing';
        $this->loadViewsFrom($views, 'print');
    }
}
