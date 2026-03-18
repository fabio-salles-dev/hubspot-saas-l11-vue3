<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deal;
use App\Models\Client;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        if (!$client) {
            $this->command->warn('Nenhum client encontrado. Criando 10 deals com clientes novos.');
            // Se não houver cliente, cria 10 deals (e 10 clientes novos automaticamente pela factory)
            Deal::factory()->count(10)->create();
            return;
        }

        // Cria 10 deals vinculados especificamente ao cliente encontrado
        Deal::factory()
            ->count(10)
            ->create([
                'client_id'  => $client->id,
                'company_id' => $client->company_id,
            ]);

        $this->command->info('10 Deals criados com sucesso via Factory!');
    }
}
