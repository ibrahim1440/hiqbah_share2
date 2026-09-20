<?php

namespace App\Modules\Procurement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'origin_country' => ['required', 'string'],
            'region' => ['required', 'string'],
            'process' => ['required', 'string'],
            'farm' => ['nullable', 'string'],
            'variety' => ['nullable', 'string'],
            'altitude' => ['nullable', 'string'],
            'quantity_kg' => ['required', 'numeric', 'min:0'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'shipping_cost' => ['numeric', 'min:0'],
            'customs_cost' => ['numeric', 'min:0'],
            'currency' => ['string', 'max:5'],
            'expected_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
