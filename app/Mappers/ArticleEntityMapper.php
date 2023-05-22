<?php

namespace App\Mappers;

use App\Contracts\EntityMapper;
use App\Entities\Article;

class ArticleEntityMapper implements EntityMapper
{
    /**
     * @param array $attributes
     *
     * @return \App\Entities\Article
     */
    public function map(array $attributes): Article
    {
        return new Article(
            id: $attributes['id'] ?? null,
            user_id: $attributes['user_id'] ?? null,
            title: $attributes['title'] ?? null,
            body: $attributes['body'] ?? null,
            published_at: $attributes['published_at'] ?? null,
        );
    }
}
