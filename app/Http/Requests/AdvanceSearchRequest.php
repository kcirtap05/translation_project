<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
            'locale' => 'sometimes|string|max:10',
            'has_description' => 'sometimes|boolean',
            'created_after' => 'sometimes|date',
            'created_before' => 'sometimes|date',
            'updated_after' => 'sometimes|date',
            'updated_before' => 'sometimes|date',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|in:key,created_at,updated_at',
            'sort_order' => 'sometimes|in:asc,desc',
        ];
    }
}
