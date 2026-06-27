<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Build the income statement for a tenant over a date range.
     *
     * @return array{
     *     from: Carbon, to: Carbon,
     *     revenue_lines: array<int, array{code: string, name: string, balance: float}>,
     *     expense_lines: array<int, array{code: string, name: string, balance: float}>,
     *     total_revenue: float, total_expenses: float, net_income: float
     * }
     */
    public function incomeStatement(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $balances = $this->accountBalances($tenant, $from, $to);

        $revenue = $balances->where('type', AccountType::Revenue);
        $expenses = $balances->where('type', AccountType::Expense);

        $totalRevenue = round((float) $revenue->sum('balance'), 2);
        $totalExpenses = round((float) $expenses->sum('balance'), 2);

        return [
            'from' => $from,
            'to' => $to,
            'revenue_lines' => $revenue->values()->all(),
            'expense_lines' => $expenses->values()->all(),
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => round($totalRevenue - $totalExpenses, 2),
        ];
    }

    /**
     * Build the balance sheet for a tenant as of a given date.
     *
     * @return array{
     *     as_of: Carbon,
     *     asset_lines: array<int, array{code: string, name: string, balance: float}>,
     *     liability_lines: array<int, array{code: string, name: string, balance: float}>,
     *     equity_lines: array<int, array{code: string, name: string, balance: float}>,
     *     total_assets: float, total_liabilities: float, total_equity: float,
     *     total_liabilities_equity: float, balanced: bool
     * }
     */
    public function balanceSheet(Tenant $tenant, Carbon $asOf): array
    {
        $balances = $this->accountBalances($tenant, null, $asOf);

        $assets = $balances->where('type', AccountType::Asset);
        $liabilities = $balances->where('type', AccountType::Liability);
        $equityAccounts = $balances->where('type', AccountType::Equity);

        // Retained earnings = cumulative net income (revenue - expenses) to date.
        $retainedEarnings = round(
            (float) $balances->where('type', AccountType::Revenue)->sum('balance')
            - (float) $balances->where('type', AccountType::Expense)->sum('balance'),
            2,
        );

        $equityLines = $equityAccounts->values()->all();
        $equityLines[] = [
            'code' => '3900',
            'name' => 'الأرباح المحتجزة',
            'type' => AccountType::Equity,
            'balance' => $retainedEarnings,
        ];

        $totalAssets = round((float) $assets->sum('balance'), 2);
        $totalLiabilities = round((float) $liabilities->sum('balance'), 2);
        $totalEquity = round((float) $equityAccounts->sum('balance') + $retainedEarnings, 2);
        $totalLiabilitiesEquity = round($totalLiabilities + $totalEquity, 2);

        return [
            'as_of' => $asOf,
            'asset_lines' => $assets->values()->all(),
            'liability_lines' => $liabilities->values()->all(),
            'equity_lines' => $equityLines,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'balanced' => abs($totalAssets - $totalLiabilitiesEquity) < 0.01,
        ];
    }

    /**
     * Compute the signed balance of every tenant account, optionally constrained by entry date.
     *
     * @return Collection<int, array{code: string, name: string, type: AccountType, balance: float}>
     */
    protected function accountBalances(Tenant $tenant, ?Carbon $from, ?Carbon $to): Collection
    {
        $sums = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->where('je.tenant_id', $tenant->id)
            ->when($from, fn ($query) => $query->whereDate('je.entry_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('je.entry_date', '<=', $to))
            ->groupBy('jl.account_id')
            ->select('jl.account_id', DB::raw('SUM(jl.debit) as debit'), DB::raw('SUM(jl.credit) as credit'))
            ->get()
            ->keyBy('account_id');

        return $tenant->accounts()->orderBy('code')->get()->map(function (Account $account) use ($sums) {
            $row = $sums->get($account->id);
            $debit = (float) ($row->debit ?? 0);
            $credit = (float) ($row->credit ?? 0);

            $balance = $account->normal_balance === 'debit'
                ? $debit - $credit
                : $credit - $debit;

            return [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => round($balance, 2),
            ];
        });
    }
}
