<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\RecognitionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RecognizeRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:recognize {--month= : Target month in Y-m format (defaults to current month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recognize deferred revenue for eligible invoices across every tenant (end of month simulation).';

    /**
     * Execute the console command.
     */
    public function handle(RecognitionService $recognition): int
    {
        $month = $this->option('month');
        $period = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $totalCount = 0;
        $totalAmount = 0.0;

        Tenant::query()->each(function (Tenant $tenant) use ($recognition, $period, &$totalCount, &$totalAmount) {
            $result = $recognition->recognizeForTenant($tenant, $period);
            $totalCount += $result['count'];
            $totalAmount += $result['amount'];
            $this->line("Tenant #{$tenant->id} ({$tenant->name}): {$result['count']} invoice(s), {$result['amount']} recognized.");
        });

        $this->info("Recognized {$totalCount} invoice(s) totaling {$totalAmount} for {$period->format('Y-m')}.");

        return self::SUCCESS;
    }
}
