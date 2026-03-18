<?php
// app/Console/Commands/HubspotSnapshotCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HubspotService;
use App\Services\HubspotSnapshotService;

class HubspotSnapshotCommand extends Command
{
    protected $signature = 'hubspot:snapshot';
    protected $description = 'Salva snapshot diário do HubSpot';

    public function __construct(
        protected HubspotService $hubspot,
        protected HubspotSnapshotService $snapshotService,
        
    ) {
        parent::__construct();
    }
    

    public function handle()
    {

        $overview = $this->hubspot->getAccountOverview();

        if (!$overview) {
            $this->error('Não foi possível obter o overview da conta HubSpot.');
            return Command::FAILURE;
        }

        $this->snapshotService->createFromOverview($overview);

        $this->info('✅ Snapshot salvo com sucesso');
        return Command::SUCCESS;
    }
}
