<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Database\Connection;

class UsersSeeder extends Seeder
{
    /**
     * @param \Illuminate\Database\Connection $connection
     */
    public function __construct(
        protected Connection $connection
    ) {
        //
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = Collection::times(10)
            ->map(fn() => [
                'name' => fake()->name,
                'email' => fake()->email,
            ])
            ->all();

        $this->connection->table('users')->insert($records);
    }
}
