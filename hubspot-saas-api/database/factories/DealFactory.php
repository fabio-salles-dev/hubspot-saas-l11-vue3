<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'      => fake()->sentence(3), // Gera um título com 3 palavras
            'value'      => fake()->randomFloat(2, 500, 10000), // Valor entre 500 e 10.000
            'status'     => fake()->randomElement(['open', 'closed', 'pending']),
            'client_id'  => \App\Models\Client::factory(), // Cria um cliente novo se não for passado um
            'company_id' => 1, // Ou use \App\Models\Company::factory() se tiver essa model
        ];
    }
}
