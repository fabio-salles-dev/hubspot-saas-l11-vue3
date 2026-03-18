<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HubspotSnapshot;
use Carbon\Carbon;

class HubspotSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $portals = [
            [
                'portal_id'    => 50923702,
                'company_name' => 'Minha Empresa',
                'region'       => 'na1',
                'timezone'     => 'America/Sao_Paulo',
            ],
            [
                'portal_id'    => 12345678,
                'company_name' => 'Outra Empresa',
                'region'       => 'eu1',
                'timezone'     => 'Europe/Lisbon',
            ],
        ];

        foreach ($portals as $portal) {
            // Cria snapshots dos últimos 7 dias
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::now()->subDays($i)->toDateString();

                HubspotSnapshot::updateOrCreate(
                    [
                        'portal_id'     => $portal['portal_id'],
                        'snapshot_date' => $date,
                    ],
                    [
                        'company_name' => $portal['company_name'],
                        'region'       => $portal['region'],
                        'timezone'     => $portal['timezone'],
                        'contacts'     => rand(5, 50),   // números aleatórios para teste
                        'companies'    => rand(2, 20),
                        'deals'        => rand(1, 10),
                    ]
                );
            }
        }

        $this->command->info('✅ HubSpot snapshots de histórico seeded com sucesso!');
    }
}
