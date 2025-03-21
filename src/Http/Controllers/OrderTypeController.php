<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\OrderType;
use FluxErp\Services\OrderTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(OrderType::class);
    }

    public function create(Request $request, OrderTypeService $orderTypeService): JsonResponse
    {
        $orderType = $orderTypeService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $orderType,
            statusMessage: 'order type created'
        );
    }

    public function delete(string $id, OrderTypeService $orderTypeService): JsonResponse
    {
        $response = $orderTypeService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, OrderTypeService $orderTypeService): JsonResponse
    {
        $response = $orderTypeService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
