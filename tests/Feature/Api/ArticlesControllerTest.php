<?php

namespace Api;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Cache\Repository as Cache;

class ArticlesControllerTest extends TestCase
{
    use RefreshDatabase;

    const DUMMY_SEARCH_TERM = 'my-search-term';

    /**
     * @var \Illuminate\Database\Connection
     */
    protected Connection $db;

    /**
     * @var \Illuminate\Contracts\Cache\Repository|\Illuminate\Cache\Repository
     */
    protected Cache $cache;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->app->get(Connection::class);

        $this->cache = $this->app->get(Cache::class);

        $this->cache->flush(); // clear cache after each test
    }

    /**
     * @return void
     */
    public function test_index_shows_only_published_articles(): void
    {
        $this->withArticles(number: 30);

        $publishedCount = $this->db->table('articles')->whereNotNull('published_at')->count();

        $this
            ->getJson(route('articles.index'))
            ->assertOk()
            ->assertJsonCount($publishedCount);
    }

    /**
     * @dataProvider searchTermProvider
     *
     * @param $term
     *
     * @return void
     */
    public function test_articles_search_is_cached($term): void
    {
        $this->withArticles(number: 20);

        $this
            ->getJson(route('articles.index', ['search' => $term]))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'title', 'email'
                ]
            ]);

        $this->assertTrue(
            $this->cache->tags('api.articles.index')->has($term)
        );
    }

    /**
     * @dataProvider failedInputsProvider
     *
     * @param array $inputs
     * @param array $errors
     *
     * @return void
     */
    public function test_store_failed_validation(array $inputs, array $errors): void
    {
        $this
            ->postJson(route('articles.store'), $inputs)
            ->assertJsonValidationErrors($errors);
    }

    /**
     * @return void
     */
    public function test_successful_store(): void
    {
        $this->withUsers();

        $this->assertDatabaseCount('articles', 0);

        $this->warmupCache();

        $this
            ->postJson(route('articles.store'), $snapshot = [
                'user_id' => $this->db->table('users')->value('id'),
                'title' => 'Article Title',
                'body' => 'Article Body',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id']);

        $this->assertDatabaseHas('articles', $snapshot);

        $this->assertCacheWasCleared();
    }

    /**
     * @return void
     */
    public function test_update_non_existent_article(): void
    {
        $this->withArticles(number: 5);

        $this
            ->putJson(route('articles.update', ['id' => 'non-existent-id']))
            ->assertNotFound();
    }

    /**
     * @return void
     */
    public function test_update_article(): void
    {
        $this->withUsers(number: 1);

        $id = $this->db->table('articles')->insertGetId([
            'user_id' => $this->db->table('users')->value('id'),
            'title' => 'Article Title',
            'body' => 'Article Body',
            'published_at' => Carbon::now()->subWeek()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('articles', 1);

        $this->warmupCache();

        $this
            ->putJson(route('articles.update', compact('id')), $attributes = [
                'title' => 'Fresh Article Title',
                'body' => 'Fresh Article Body',
            ])
            ->assertOk()
            ->assertExactJson($attributes);

        $this->assertDatabaseHas('articles', compact('id') + $attributes);

        $this->assertCacheWasCleared();
    }

    /**
     * @return void
     */
    public function test_update_with_same_attributes(): void
    {
        $this->withUsers(number: 1);

        $id = $this->db->table('articles')->insertGetId([
            'user_id' => $this->db->table('users')->value('id'),
            'title' => 'Article Title',
            'body' => 'Article Body',
            'published_at' => Carbon::now()->subWeek()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('articles', 1);

        $this->warmupCache();

        $this
            ->putJson(route('articles.update', compact('id')), [
                'title' => 'Article Title',
                'body' => 'Article Body',
            ])
            ->assertOk()
            ->assertExactJson([]); // nothing to update

        $this->assertCacheWasCleared();
    }

    /**
     * @return void
     */
    public function test_destroy_non_existent_article(): void
    {
        $this->withArticles(number: 5);

        $this
            ->deleteJson(route('articles.destroy', ['id' => 'non-existent-id']))
            ->assertNotFound();
    }

    /**
     * @return void
     */
    public function test_destroy_published_article()
    {
        $this->withUsers(number: 1);

        $id = $this->db->table('articles')->insertGetId([
            'user_id' => $this->db->table('users')->value('id'),
            'title' => 'Article Title',
            'body' => 'Article Body',
            'published_at' => Carbon::now()->subWeek()->toDateTimeString()
        ]);

        $this->assertDatabaseCount('articles', 1);

        $this->warmupCache();

        $this
            ->deleteJson(route('articles.destroy', compact('id')))
            ->assertNoContent();

        $this->assertDatabaseHas('articles', [
            'id' => $id,
            'published_at' => null, // unpublishes the article
        ]);

        $this->assertCacheWasCleared();
    }

    /**
     * @return void
     */
    public function test_destroy_unpublished_article()
    {
        $this->withUsers(number: 1);

        $id = $this->db->table('articles')->insertGetId([
            'user_id' => $this->db->table('users')->value('id'),
            'title' => 'Article Title',
            'body' => 'Article Body',
            'published_at' => null
        ]);

        $this->assertDatabaseCount('articles', 1);

        $this->warmupCache();

        $this
            ->deleteJson(route('articles.destroy', compact('id')))
            ->assertNoContent();

        $this->assertDatabaseCount('articles', 0);

        $this->assertCacheWasCleared();
    }

    /**
     * @param string $searchTerm
     *
     * @return void
     */
    public function warmupCache(string $searchTerm = self::DUMMY_SEARCH_TERM): void
    {
        $this->getJson(route('articles.index', ['search' => $searchTerm])); // trigger cache

        $this->assertTrue(
            $this->cache->tags('api.articles.index')->has($searchTerm)
        );
    }

    /**
     * @param string $searchTerm
     *
     * @return void
     */
    public function assertCacheWasCleared(string $searchTerm = self::DUMMY_SEARCH_TERM): void
    {
        $this->assertTrue(
            $this->cache->tags('api.articles.index')->missing($searchTerm)
        );
    }


    /**
     * @return array
     */
    public static function searchTermProvider(): array
    {
        return [
            [null],
            ['ipsum'],
            ['lorem'],
        ];
    }

    /**
     * @return array
     */
    public static function failedInputsProvider(): array
    {
        return [
            [[], ['user_id', 'title', 'body']],
            [
                [
                    'user_id' => 'user-id',
                    'title' => fake()->realTextBetween(120, 255),
                    'body' => fake()->realTextBetween(1010, 2000),
                    'published_at' => 'date-time'
                ],
                ['user_id', 'title', 'body', 'published_at']
            ]
        ];
    }

    /**
     * @param array $attributes
     * @param int $number
     *
     * @return void
     */
    public function withArticles(array $attributes = [], int $number = 1): void
    {
        $this->withUsers(number: 5);

        $users = $this->db->table('users')->inRandomOrder()->pluck('id');

        $records = Collection::times($number)
            ->map(fn() => array_merge([
                'user_id' => fake()->randomElement($users),
                'title' => fake()->text(200),
                'body' => fake()->text(1000),
                'published_at' => fake()->boolean ? fake()->dateTime() : null

            ], $attributes))
            ->all();

        $this->db->table('articles')->insert($records);
    }

    /**
     * @param array $attributes
     * @param int $number
     *
     * @return void
     */
    public function withUsers(array $attributes = [], int $number = 1): void
    {
        $records = Collection::times($number)
            ->map(fn() => array_merge([
                'name' => fake()->name,
                'email' => fake()->email,
            ], $attributes))
            ->all();

        $this->db->table('users')->insert($records);
    }
}
