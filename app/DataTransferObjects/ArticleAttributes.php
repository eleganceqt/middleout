<?php

namespace App\DataTransferObjects;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

final readonly class ArticleAttributes implements Arrayable
{
    public string $userId;
    public string $title;
    public string $body;
    public ?string $publishedAt;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        foreach ($attributes as $property => $value) {
            if (property_exists($this, Str::camel($property))) {
                $this->{Str::camel($property)} = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC);

        return (new Collection($properties))
            ->filter(fn(ReflectionProperty $property) => $property->isInitialized($this))
            ->mapWithKeys(fn(ReflectionProperty $property) => [Str::snake($property->getName()) => $this->{$property->getName()}])
            ->all();
    }
}
