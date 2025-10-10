<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocaleRequest extends FormRequest
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
    public function rules()
    {
        switch ($this->method()) {
            CASE 'POST': 
                return [
                    'name' => 'string|max:100',
                    'code' => 'string|max:10|unique:locales,code'
                ];
                break;
            CASE 'PUT':
                return [
                    'id' => 'sometimes|integer|exists:locales,id',
                    'name' => 'string|max:100',
                    'code' => 'string|max:10|unique:locales,code'
                ];
                break;
        }  
    }
}
