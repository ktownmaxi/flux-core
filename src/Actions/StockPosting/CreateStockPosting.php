<?php

namespace FluxErp\Actions\StockPosting;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateStockPostingRequest;
use FluxErp\Models\StockPosting;

class CreateStockPosting extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateStockPostingRequest())->rules();
    }

    public static function models(): array
    {
        return [StockPosting::class];
    }

    public function performAction(): StockPosting
    {
        $this->data['stock'] = $this->getLatestStock(
            $this->data['warehouse_id'], $this->data['product_id'], $this->data['posting']
        );

        $stockPosting = new StockPosting($this->data);
        $stockPosting->save();

        return $stockPosting;
    }

    private function getLatestStock(int $warehouseId, int $productId, float $posting): float
    {
        $latestPosting = StockPosting::query()
            ->where('warehouse_id', '=', $warehouseId)
            ->where('product_id', '=', $productId)
            ->latest('id')
            ->first();

        if (empty($latestPosting->stock)) {
            return $posting;
        }

        return $latestPosting->stock + $posting;
    }
}
