<?php

namespace App\Console\Commands;

use App\Services\SlaCalculatorService;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:check-breaches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan active tickets for SLA warnings (75%) and breaches (100%), dispatching alerts idempotently.';

    /**
     * Execute the console command.
     */
    public function handle(SlaCalculatorService $slaCalculator): int
    {
        $this->info('Scanning tickets for SLA breaches and warnings...');

        $stats = $slaCalculator->checkBreachesAndWarnings();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Tickets Scanned', $stats['tickets_scanned']],
                ['Warnings Sent (75%)', $stats['warnings_sent']],
                ['Breaches Sent (100%)', $stats['breaches_sent']],
            ]
        );

        return self::SUCCESS;
    }
}
