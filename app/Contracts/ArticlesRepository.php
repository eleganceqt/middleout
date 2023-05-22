<?php

namespace App\Contracts;

use App\Entities\Article;

interface ArticlesRepository
{
    /**
     * @param array $attributes
     *
     * @return string
     */
    public function create(array $attributes);

    /**
     * @param string $id
     *
     * @return \App\Entities\Article|null
     */
    public function find(string $id): ?Article;

    /**
     * @param string $id
     *
     * @return \App\Entities\Article
     */
    public function findOrFail(string $id): Article;

    /**
     * @param string $id
     * @param array $attributes
     *
     * @return int
     */
    public function updateById(string $id, array $attributes);

    /**
     * @param string $id
     *
     * @return int
     */
    public function destroy(string $id);

    /**
     * @param string|null $term
     * @param array $columns
     *
     * @return array
     */
    public function getBySearchTerm(?string $term = null, array $columns = ['*']);
}
