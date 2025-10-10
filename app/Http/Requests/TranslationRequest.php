<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationRequest extends FormRequest
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
        switch ($this->method()) {
            CASE 'POST': 
                return [
                    'key' => 'string|max:255',
                    'description' => 'string|max:255',
                    'tags' => 'array',
                    'tags.*' => 'string|max:50',
                    'translations' => 'required|array',
                    'translations.*' => 'required|string|max:255',
                ];
                break;
            CASE 'PUT':
                return [
                    'id' => 'sometimes|integer|exists:tags,id',
                    'key' => 'string|max:255',
                    'description' => 'string|max:255',
                    'tags' => 'array',
                    'tags.*' => 'string|max:50',
                    'translations' => 'required|array',
                    'translations.*' => 'required|string|max:255',
                ];
                break;
        }
        
    }
}
