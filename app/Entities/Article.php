<?php

namespace App\Entities;

use Illuminate\Contracts\Support\Arrayable;

final readonly class Article implements Arrayable
{
    protected ?User $user;

    /**
     * @param string|null $id
     * @param string|null $user_id
     * @param string|null $title
     * @param string|null $body
     * @param string|null $published_at
     */
    public function __construct(
        public ?string $id,
        public ?string $user_id,
        public ?string $title,
        public ?string $body,
        public ?string $published_at,
    ) {
        //
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return ! is_null($this->published_at);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'body' => $this->body,
            'published_at' => $this->published_at,
        ];
    }
}
