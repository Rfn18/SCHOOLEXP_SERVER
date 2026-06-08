<?php

namespace Database\Factories;

use App\Models\Documentation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentationFactory extends Factory
{
    protected $model = Documentation::class;

    public function definition(): array
{
    return [
        'file_path' => 'documentations/' . fake()->uuid() . '.jpg',
        'alt_text' => fake()->sentence(3),
        'type' => 'medium',
        'gallery_id' => 99, 
        'soft_order' => fake()->numberBetween(0, 100),
    ];
}
}