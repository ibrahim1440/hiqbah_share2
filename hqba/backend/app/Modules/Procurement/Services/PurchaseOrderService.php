<?php

namespace App\Modules\Procurement\Services;

use App\Modules\Procurement\Enums\PurchaseOrderStatus;
use App\Modules\Procurement\Events\PurchaseOrderApproved;
use App\Modules\Procurement\Models\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderService
{
    private const ALLOWED_TRANSITIONS = [
        'draft' => ['pending_approval'],
        'pending_approval' => ['approved', 'cancelled'],
        'approved' => ['ordered', 'cancelled'],
        'ordered' => ['shipped', 'cancelled'],
        'shipped' => ['in_customs'],
        'in_customs' => ['received'],
    ];

    public function list(): LengthAwarePaginator
    {
        return QueryBuilder::for(PurchaseOrder::class)
            ->allowedFilters([
                AllowedFilter::exact('supplier_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('created_by'),
                'po_number',
            ])
            ->allowedSorts(['po_number', 'created_at', 'expected_date', 'total_cost'])
            ->allowedIncludes(['supplier', 'creator', 'approver'])
            ->defaultSort('-created_at')
            ->paginate(request('per_page', 15));
    }

    public function create(array $data, int $userId): PurchaseOrder
    {
        $data['po_number'] = PurchaseOrder::generatePoNumber();
        $data['created_by'] = $userId;
        $data['status'] = $data['status'] ?? 'draft';

        $purchaseOrder = new PurchaseOrder($data);
        $purchaseOrder->calculateTotalCost();
        $purchaseOrder->save();

        return $purchaseOrder;
    }

    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        $purchaseOrder->update($data);

        if (
            array_key_exists('quantity_kg', $data)
            || array_key_exists('price_per_kg', $data)
            || array_key_exists('shipping_cost', $data)
            || array_key_exists('customs_cost', $data)
        ) {
            $purchaseOrder->calculateTotalCost();
            $purchaseOrder->save();
        }

        return $purchaseOrder->fresh();
    }

    public function delete(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->delete();
    }

    public function approve(PurchaseOrder $purchaseOrder, int $approverId): PurchaseOrder
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => ['Purchase order must be in pending_approval status to be approved.'],
            ]);
        }

        $purchaseOrder->update([
            'approved_by' => $approverId,
            'approved_at' => now(),
            'status' => PurchaseOrderStatus::Approved,
        ]);

        PurchaseOrderApproved::dispatch($purchaseOrder);

        return $purchaseOrder->fresh();
    }

    public function updateStatus(PurchaseOrder $purchaseOrder, string $newStatus): PurchaseOrder
    {
        $currentStatus = $purchaseOrder->status->value;
        $allowedNext = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (! in_array($newStatus, $allowedNext)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from {$currentStatus} to {$newStatus}."],
            ]);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::from($newStatus),
        ]);

        return $purchaseOrder->fresh();
    }
}
