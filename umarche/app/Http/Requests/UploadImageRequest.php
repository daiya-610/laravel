<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'image' => 'image|mimes:jpg,jpeg,png|max:2048',
            'files.*.images' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }
    
    public function messages(): array
    {
        return [
            'image.required' => '画像を選択してください。',
            'image.image' => '指定されたファイルが画像ではありません。',
            'image.mimes' => '指定された拡張子(jpg/jpeg/png)ではありません。',
            'image.max' => 'ファイルサイズは2MB以内にしてください。',
        ];
    }
}
