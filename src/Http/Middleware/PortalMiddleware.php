<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\Address;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PortalMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (request()->isPortal()) {
            config(['filesystems.disks.public.url' => config('flux.portal_domain') . '/storage']);
            resolve_static(SerialNumber::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->whereHas(
                            'address',
                            fn (Builder $query) => $query->where('contact_id', auth()->user()->contact->id)
                        )->orWhereHas(
                            'orderPosition.order',
                            fn (Builder $query) => $query->where('contact_id', auth()->user()->contact->id)
                        );
                    });
                },
            ]);
            resolve_static(Order::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query) {
                    $query->where('contact_id', auth()->user()->contact->id)
                        ->where(fn (Builder $query) => $query->where('is_locked', true)
                            ->orWhere('is_imported', true)
                        );
                },
            ]);
            resolve_static(OrderPosition::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query) {
                    $query->whereRelation('order', 'contact_id', auth()->user()->contact->id);
                },
            ]);
            resolve_static(Ticket::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query) {
                    $query->where('authenticatable_type', morph_alias(Address::class))
                        ->where('authenticatable_id', auth()->user()->id);
                },
            ]);
            resolve_static(Cart::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where(function (Builder $query) {
                            $query->where('authenticatable_id', auth()->id())
                                ->where('authenticatable_type', auth()->user()?->getMorphClass());
                        })
                            ->orWhere('session_id', session()->id())
                            ->orWhere('is_portal_public', true);
                    });
                },
            ]);
            resolve_static(CartItem::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query) {
                    $query->whereHas('cart', function (Builder $query) {
                        $query->where(function (Builder $query) {
                            $query->where(function (Builder $query) {
                                $query->where('authenticatable_id', auth()->id())
                                    ->where('authenticatable_type', auth()->user()?->getMorphClass());
                            })
                                ->orWhere('session_id', session()->id())
                                ->orWhere('is_portal_public', true);
                        });
                    });
                },
            ]);
            resolve_static(Cart::class, 'deleting', [
                'callback' => function (Cart $cart) {
                    if (
                        (
                            is_null($cart->authenticatable_type)
                            && is_null($cart->authenticatable_id)
                            && $cart->session_id === session()->id()
                        )
                        || (
                            $cart->authenticatable_type === auth()->user()?->getMorphClass()
                            && $cart->authenticatable_id === auth()->id()
                        )
                    ) {
                        return $cart->deleteQuietly();
                    }

                    return false;
                },
            ]);

            config(['livewire.layout' => 'flux::components.layouts.portal']);
            config(['app.url' => config('flux.portal_domain')]);
        }

        return $next($request);
    }
}
