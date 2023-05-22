<?php

namespace App\Repositories;

use App\Entities\Article;
use App\Contracts\EntityMapper;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use App\Exceptions\ArticleNotFoundException;
use App\Contracts\ArticlesRepository as ArticlesRepositoryContract;

class ArticlesRepository implements ArticlesRepositoryContract
{
    /**
     * @param \Illuminate\Database\Connection $connection
     * @param \App\Contracts\EntityMapper $mapper
     * @param string $table
     */
    public function __construct(
        protected Connection $connection,
        protected EntityMapper $mapper,
        protected string $table,
    ) {
        //
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    public function create(array $attributes): string
    {
        return $this->connection->table($this->table)
            ->insertGetId($attributes);
    }

    /**
     * @param string $id
     *
     * @return \App\Entities\Article|null
     */
    public function find(string $id): ?Article
    {
        $attributes = (array) $this->connection->table($this->table)
            ->where('id', $id)
            ->first();

        if ($attributes) {
            return $this->mapper->map($attributes);
        }

        return null;
    }

    /**
     * @param string $id
     *
     * @return \App\Entities\Article
     */
    public function findOrFail(string $id): Article
    {
        $article = $this->find($id);

        if (is_null($article)) {
            throw new ArticleNotFoundException();
        }

        return $article;
    }

    /**
     * @param string $id
     * @param array $attributes
     *
     * @return int
     */
    public function updateById(string $id, array $attributes): int
    {
        return $this->connection->table($this->table)
            ->where('id', $id)
            ->update($attributes);
    }

    /**
     * @param string $id
     *
     * @return int
     */
    public function destroy(string $id): int
    {
        return $this->connection->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    /**
     * @param string|null $term
     * @param array $columns
     *
     * @return array
     */
    public function getBySearchTerm(?string $term = null, array $columns = ['*']): array
    {
        return $this->connection->table($this->table)
            ->join('users', 'users.id', 'articles.user_id')
            ->whereNotNull('articles.published_at')
            ->unless(blank($term), fn(Builder $query) => $query
                ->where(fn(Builder $query) => $query
                    ->where('articles.title', 'like', '%' . $term . '%')
                    ->orWhere('articles.body', 'like', '%' . $term . '%')
                )
            )
            ->orderByDesc('articles.published_at')
            ->get($columns)
            ->all();
    }
}
