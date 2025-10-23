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
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'different:' . $this->user()->id],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Alıcı Kullanıcı Seç',
            'receiver_id.integer' => 'Geçersiz Alıcı ID',
            'receiver_id.exists' => 'Seçilen alıcı bulunamadı.',
            'receiver_id.different' => 'Kendinize mesaj gönderemezsiniz.',
            'title.required' => 'Başlık alanı gereklidir.',
            'title.string' => 'Başlık bir metin olmalıdır.',
            'title.max' => 'Başlık 255 karakterden fazla olamaz.',
            'content.required' => 'İçerik alanı gereklidir.',
        ];
    }
}
