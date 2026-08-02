<?php

namespace App\Http\Requests;

use App\Models\Documentation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'image' => $isUpdate
                        ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120'
                        : 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'alt_text' => 'required|string|max:5000',
            'witdh' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'gallery_id' => 'required|exists:doc_galleries,id',
            'soft_order' => 'required|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'image.required'    => 'Foto wajib diupload.',
            'image.max'         => 'Ukuran foto maksimal 5MB.',
            'type.in'           => 'Tipe harus small, medium, atau large.',
            'gallery_id.exists' => 'Gallery tidak ditemukan.'
        ];
    }
}
