<?php

namespace App\Entities;

use Illuminate\Contracts\Support\Arrayable;

class User implements Arrayable
{
    /**
     * @param string|null $id
     * @param string|null $name
     * @param string|null $email
     */
    public function __construct(
        public ?string $id,
        public ?string $name,
        public ?string $email,
    ) {
        //
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
