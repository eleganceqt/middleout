<?php

namespace App\Services;

use Carbon\Carbon;
use App\Support\ChangesDetector;
use Illuminate\Database\Connection;
use App\Contracts\ArticlesRepository;
use App\DataTransferObjects\ArticleAttributes;
use Illuminate\Contracts\Cache\Repository as Cache;

class ArticlesService
{
    const CACHE_TAGS = 'api.articles.index';

    public function __construct(
        protected Connection $connection,
        protected ArticlesRepository $repository,
        protected ChangesDetector $detector,
        /**
         * @var \Illuminate\Contracts\Cache\Repository|\Illuminate\Cache\Repository
         */
        protected Cache $cache
    ) {
        //
    }

    /**
     * @param \App\DataTransferObjects\ArticleAttributes $attributes
     *
     * @return string
     * @throws \Throwable
     */
    public function create(ArticleAttributes $attributes): string
    {
        $id = $this->connection->transaction(
            fn() => $this->repository->create($attributes->toArray())
        );

        $this->cache->tags(static::CACHE_TAGS)->flush();

        return $id;
    }


    /**
     * @param string $id
     * @param \App\DataTransferObjects\ArticleAttributes $attributes
     *
     * @return array
     * @throws \Throwable
     */
    public function update(string $id, ArticleAttributes $attributes): array
    {
        $changes = $this->connection->transaction(function () use ($id, $attributes) {

            $article = $this->repository->findOrFail($id);

            $changes = $this->detector->diffs(
                original: $article->toArray(),
                actual: $attributes->toArray()
            );

            if ($changes) {
                $this->repository->updateById($id, $changes);
            }

            return $changes;
        });

        $this->cache->tags(static::CACHE_TAGS)->flush();

        return $changes;
    }

    /**
     * @param string $id
     *
     * @return void
     * @throws \Throwable
     */
    public function destroy(string $id): void
    {
        $this->connection->transaction(function () use ($id) {

            $article = $this->repository->findOrFail($id);

            $article->isPublished()
                ? $this->repository->updateById($id, ['published_at' => null])
                : $this->repository->destroy($id);

        });

        $this->cache->tags(static::CACHE_TAGS)->flush();
    }

    /**
     * @param string|null $term
     *
     * @return array
     */
    public function getBySearchTerm(?string $term = null): array
    {
        return $this->cache
            ->tags(static::CACHE_TAGS)
            ->remember(
                key: $term,
                ttl: Carbon::now()->addSeconds(60),
                callback: fn() => $this->repository->getBySearchTerm($term, ['articles.title', 'users.email'])
            );
    }
}
