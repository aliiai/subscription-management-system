<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate {--month= : Target month in Y-m format (defaults to current month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active subscriptions across every tenant.';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billing): int
    {
        $month = $this->option('month');
        $period = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $total = 0;

        Tenant::query()->each(function (Tenant $tenant) use ($billing, $period, &$total) {
            $created = $billing->generateForTenant($tenant, $period);
            $total += $created;
            $this->line("Tenant #{$tenant->id} ({$tenant->name}): {$created} invoice(s).");
        });

        $this->info("Generated {$total} invoice(s) for {$period->format('Y-m')}.");

        return self::SUCCESS;
    }
}
