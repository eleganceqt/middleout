<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\DataTransferObjects\ArticleAttributes;

class ArticleStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:1000',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    /**
     * @return \App\DataTransferObjects\ArticleAttributes
     */
    public function toArticlesAttributes(): ArticleAttributes
    {
        return new ArticleAttributes([
            'userId' => $this->input('user_id'),
            'title' => $this->input('title'),
            'body' => $this->input('body'),
            'publishedAt' => $this->input('published_at'),
        ]);
    }
}
