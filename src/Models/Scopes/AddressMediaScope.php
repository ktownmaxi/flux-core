<?php

namespace FluxErp\Models\Scopes;

use FluxErp\Models\Address;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;

class AddressMediaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Don't apply scope if no user is authenticated
        if (! Auth::hasUser()) {
            return;
        }

        // Only Apply Scope if User is an Address
        if (! Auth::user() instanceof Address) {
            return;
        }

        $builder->where(function (Builder $query): void {
            $query->whereNot('model_type', morph_alias(Order::class))
                ->orWhere(function (Builder $query): void {
                    $query->where('model_type', morph_alias(Order::class))
                        ->whereIn('model_id', function (QueryBuilder $query): void {
                            $query->select('id')
                                ->from('orders')
                                ->where('contact_id', Auth::user()->contact_id)
                                ->where('is_locked', true)
                                ->whereNull('deleted_at');
                        });
                });
        });
    }
}
