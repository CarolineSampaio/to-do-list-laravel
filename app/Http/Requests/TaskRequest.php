<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(function ($query) {
                        $query->where('user_id', $this->user()->id);
                    }),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório',
            'title.string' => 'O título deve ser válido',
            'title.max' => 'O título deve ter no máximo 255 caracteres',
            'description.string' => 'A descrição deve ser válida',
            'category_id.integer' => 'A categoria deve ser um número inteiro',
            'category_id.exists' => 'A categoria deve existir',
        ];
    }
}
