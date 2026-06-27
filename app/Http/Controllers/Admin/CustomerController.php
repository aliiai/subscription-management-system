<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display all customers across every company.
     */
    public function index(): View
    {
        $customers = Customer::query()
            ->with(['tenant', 'plan'])
            ->latest()
            ->get();

        return view('admin.customers.index', ['customers' => $customers]);
    }
}
