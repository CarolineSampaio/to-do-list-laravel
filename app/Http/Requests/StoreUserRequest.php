<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public $stopOnFirstFailure = true;
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
            'name' => 'required|string|min:3|max:150|regex:/^[\p{L}\s]+$/u',
            'email' => 'required|email|unique:users|max:150',
            'password' => 'required|string|min:6|max:25|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{6,25}$/'
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.string' => 'O nome deve ser um texto',
            'name.min' => 'O nome deve ter no mínimo 3 caracteres',
            'name.max' => 'O nome deve ter no máximo 150 caracteres',
            'name.regex' => 'O nome deve conter apenas letras',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email deve ser válido',
            'email.unique' => 'O email já está em uso',
            'email.max' => 'O email deve ter no máximo 150 caracteres',
            'password.required' => 'A senha é obrigatória',
            'password.string' => 'A senha deve ser válida',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'password.max' => 'A senha deve ter no máximo 25 caracteres',
            'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula, um número e um caractere especial'
        ];
    }
}
