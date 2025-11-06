<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
            'receiver_email' => ['required', 'email', 'exists:users,email'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_email.required' => 'Alıcı email adresi gereklidir.',
            'receiver_email.email' => 'Geçerli bir email adresi giriniz.',
            'receiver_email.exists' => 'Bu email adresine sahip kullanıcı bulunamadı.',

            'title.required' => 'Başlık alanı gereklidir.',
            'title.string' => 'Başlık bir metin olmalıdır.',
            'title.max' => 'Başlık 255 karakterden fazla olamaz.',
            'content.required' => 'İçerik alanı gereklidir.',
        ];
    }
}
