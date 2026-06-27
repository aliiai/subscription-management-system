<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    /**
     * Display the income statement for a selected date range.
     */
    public function incomeStatement(Request $request): View
    {
        $tenant = $this->tenant($request);

        $from = ($request->date('from') ?? now()->startOfMonth())->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $data = [
            'report' => $this->reports->incomeStatement($tenant, $from, $to),
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        ];

        if ($request->ajax()) {
            return view('company.reports._income-statement', $data);
        }

        return view('company.reports.income-statement', $data);
    }

    /**
     * Display the balance sheet as of a selected date.
     */
    public function balanceSheet(Request $request): View
    {
        $tenant = $this->tenant($request);

        $asOf = ($request->date('as_of') ?? now())->endOfDay();

        $data = [
            'report' => $this->reports->balanceSheet($tenant, $asOf),
            'filters' => [
                'as_of' => $asOf->format('Y-m-d'),
            ],
        ];

        if ($request->ajax()) {
            return view('company.reports._balance-sheet', $data);
        }

        return view('company.reports.balance-sheet', $data);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
