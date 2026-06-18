<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class HoldCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id' => 'required|exists:pos_sessions,id',
            'cart_data'  => 'required|json',
            'note'       => 'nullable|string|max:500',
        ];
    }
}
