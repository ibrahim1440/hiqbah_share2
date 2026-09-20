<?php

namespace App\Modules\Procurement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['sometimes', 'exists:suppliers,id'],
            'origin_country' => ['sometimes', 'string'],
            'region' => ['sometimes', 'string'],
            'process' => ['sometimes', 'string'],
            'farm' => ['nullable', 'string'],
            'variety' => ['nullable', 'string'],
            'altitude' => ['nullable', 'string'],
            'quantity_kg' => ['sometimes', 'numeric', 'min:0'],
            'price_per_kg' => ['sometimes', 'numeric', 'min:0'],
            'shipping_cost' => ['numeric', 'min:0'],
            'customs_cost' => ['numeric', 'min:0'],
            'currency' => ['string', 'max:5'],
            'expected_date' => ['sometimes', 'date', 'after:today'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
