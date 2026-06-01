<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScientificArticleRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'foundation_id' => 'required|exists:foundations,id',
            'author_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'file_path' => 'nullable|mimes:pdf|max:10240',
            'content' => 'required|string',
            'citations' => 'nullable|array',
            'citations.*.type' => 'required|in:QURAN,HADITH,KITAB,SAINS',
            'citations.*.source_text_arabic' => 'nullable|string',
            'citations.*.translation' => 'nullable|string',
            'citations.*.reference' => 'required|string|max:255',
            'bibliography' => 'nullable|array',
            'bibliography.*.full_citation' => 'required|string',
        ];
    }
}
