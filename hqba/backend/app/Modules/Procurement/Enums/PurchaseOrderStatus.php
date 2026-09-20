<?php

namespace App\Modules\Procurement\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Ordered = 'ordered';
    case Shipped = 'shipped';
    case InCustoms = 'in_customs';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::PendingApproval => 'بانتظار الاعتماد',
            self::Approved => 'معتمد',
            self::Ordered => 'تم الطلب',
            self::Shipped => 'تم الشحن',
            self::InCustoms => 'في الجمارك',
            self::Received => 'تم الاستلام',
            self::Cancelled => 'ملغى',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
            self::Ordered => 'Ordered',
            self::Shipped => 'Shipped',
            self::InCustoms => 'In Customs',
            self::Received => 'Received',
            self::Cancelled => 'Cancelled',
        };
    }
}
