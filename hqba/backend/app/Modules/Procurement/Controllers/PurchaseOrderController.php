<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Controllers\ApiController;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Requests\StorePurchaseOrderRequest;
use App\Modules\Procurement\Requests\UpdatePurchaseOrderRequest;
use App\Modules\Procurement\Resources\PurchaseOrderResource;
use App\Modules\Procurement\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends ApiController
{
    public function __construct(
        protected PurchaseOrderService $purchaseOrderService,
    ) {}

    public function index(): JsonResponse
    {
        $purchaseOrders = $this->purchaseOrderService->list();

        return $this->success(PurchaseOrderResource::collection($purchaseOrders));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $po = $this->purchaseOrderService->create($request->validated(), auth()->id());

        return $this->created(new PurchaseOrderResource($po));
    }

    public function show(string $purchase_order): JsonResponse
    {
        $po = PurchaseOrder::findOrFail($purchase_order);
        $po->load(['supplier', 'creator', 'approver']);

        return $this->success(new PurchaseOrderResource($po));
    }

    public function update(UpdatePurchaseOrderRequest $request, string $purchase_order): JsonResponse
    {
        $po = PurchaseOrder::findOrFail($purchase_order);
        $po = $this->purchaseOrderService->update($po, $request->validated());

        return $this->success(new PurchaseOrderResource($po));
    }

    public function destroy(string $purchase_order): JsonResponse
    {
        $po = PurchaseOrder::findOrFail($purchase_order);
        $this->purchaseOrderService->delete($po);

        return $this->noContent();
    }

    public function approve(string $purchase_order): JsonResponse
    {
        $po = PurchaseOrder::findOrFail($purchase_order);
        $po = $this->purchaseOrderService->approve($po, auth()->id());

        return $this->success(new PurchaseOrderResource($po));
    }

    public function updateStatus(Request $request, string $purchase_order): JsonResponse
    {
        $po = PurchaseOrder::findOrFail($purchase_order);

        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $po = $this->purchaseOrderService->updateStatus($po, $request->input('status'));

        return $this->success(new PurchaseOrderResource($po));
    }
}
