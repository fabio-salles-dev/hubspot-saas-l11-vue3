<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::create([
            'name' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'hubspot_id' => 'hub_001',
            'company_id' => null, // pode ajustar depois
        ]);
    }
}
