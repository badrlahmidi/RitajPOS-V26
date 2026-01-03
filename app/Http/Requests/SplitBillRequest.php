<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SplitBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'splits' => ['required', 'array', 'min:2'],
            'splits.*' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'splits.min' => 'Le fractionnement nécessite au moins 2 parts',
            'splits.*.min' => 'Chaque montant doit être supérieur à 0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $order = \App\Models\Order::find($this->route('order'));
            
            if ($order) {
                $splitTotal = array_sum($this->splits);
                
                if (abs($splitTotal - $order->total) > 0.01) {
                    $validator->errors()->add(
                        'splits',
                        "Le total des parts ({$splitTotal}) ne correspond pas au total de la commande ({$order->total})"
                    );
                }
            }
        });
    }
}
