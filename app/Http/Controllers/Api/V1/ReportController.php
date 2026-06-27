<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AccountType;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends ApiController
{
    public function __construct(protected ReportService $reports) {}

    /**
     * Income statement (subscription revenue) for a selected date range.
     */
    public function incomeStatement(Request $request): JsonResponse
    {
        $tenant = $this->tenant($request);

        $from = ($request->date('from') ?? now()->startOfMonth())->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $report = $this->reports->incomeStatement($tenant, $from, $to);

        return response()->json([
            'data' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'revenue_lines' => $this->formatLines($report['revenue_lines']),
                'expense_lines' => $this->formatLines($report['expense_lines']),
                'total_revenue' => $report['total_revenue'],
                'total_expenses' => $report['total_expenses'],
                'net_income' => $report['net_income'],
            ],
        ]);
    }

    /**
     * Balance sheet (Cash, Accounts Receivable, Deferred Revenue, ...) as of a date.
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $tenant = $this->tenant($request);

        $asOf = ($request->date('as_of') ?? now())->endOfDay();

        $report = $this->reports->balanceSheet($tenant, $asOf);

        return response()->json([
            'data' => [
                'as_of' => $asOf->toDateString(),
                'asset_lines' => $this->formatLines($report['asset_lines']),
                'liability_lines' => $this->formatLines($report['liability_lines']),
                'equity_lines' => $this->formatLines($report['equity_lines']),
                'total_assets' => $report['total_assets'],
                'total_liabilities' => $report['total_liabilities'],
                'total_equity' => $report['total_equity'],
                'total_liabilities_equity' => $report['total_liabilities_equity'],
                'balanced' => $report['balanced'],
            ],
        ]);
    }

    /**
     * Normalize report lines (resolving the AccountType enum when present) for JSON output.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    protected function formatLines(array $lines): array
    {
        return array_map(function (array $line): array {
            if (isset($line['type']) && $line['type'] instanceof AccountType) {
                $line['type'] = $line['type']->value;
            }

            return $line;
        }, $lines);
    }
}
