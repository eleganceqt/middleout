<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\DataTransferObjects\ArticleAttributes;

class ArticleUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|numeric|exists:users,id',
            'title' => 'sometimes|string|max:200',
            'body' => 'sometimes|string|max:1000',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    /**
     * @return \App\DataTransferObjects\ArticleAttributes
     */
    public function toArticlesAttributes(): ArticleAttributes
    {
        return new ArticleAttributes($this->validated());
    }
}
