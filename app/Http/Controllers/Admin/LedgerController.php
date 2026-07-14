<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    /**
     * General Ledger — main listing view with filters
     */
    public function index(Request $request)
    {
        $query = LedgerEntry::query()->with('user');

        // ── Date Range ─────────────────────────────────────────────────────
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());
        $query->whereBetween('entry_date', [$fromDate, $toDate]);

        // ── Filters ────────────────────────────────────────────────────────
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('party_type')) {
            $query->where('party_type', $request->party_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('reference_number', 'like', "%{$s}%")
                  ->orWhere('party_name', 'like', "%{$s}%")
                  ->orWhere('entry_number', 'like', "%{$s}%");
            });
        }

        $query->orderByDesc('entry_date')->orderByDesc('id');

        $entries = $query->paginate(25)->appends($request->query());

        // ── Period Summary ─────────────────────────────────────────────────
        $summaryQuery = LedgerEntry::whereBetween('entry_date', [$fromDate, $toDate]);

        // clone filters onto summary
        if ($request->filled('account_type'))      $summaryQuery->where('account_type', $request->account_type);
        if ($request->filled('transaction_type'))  $summaryQuery->where('transaction_type', $request->transaction_type);
        if ($request->filled('payment_method'))    $summaryQuery->where('payment_method', $request->payment_method);
        if ($request->filled('party_type'))        $summaryQuery->where('party_type', $request->party_type);

        $totalCredit  = (clone $summaryQuery)->sum('credit');
        $totalDebit   = (clone $summaryQuery)->sum('debit');
        $netBalance   = $totalCredit - $totalDebit;

        // ── Account Breakdown (for current period / filters) ───────────────
        $accountBreakdown = (clone $summaryQuery)
            ->select('account_type',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit'),
                DB::raw('COUNT(*) as entry_count'))
            ->groupBy('account_type')
            ->orderByDesc('total_credit')
            ->get();

        return view('admin.ledger.index', compact(
            'entries',
            'fromDate',
            'toDate',
            'totalCredit',
            'totalDebit',
            'netBalance',
            'accountBreakdown'
        ));
    }

    /**
     * Show a single ledger entry detail
     */
    public function show(LedgerEntry $ledger)
    {
        $ledger->load('user');
        return view('admin.ledger.show', compact('ledger'));
    }

    /**
     * Account Summary / Chart of Accounts
     */
    public function accounts(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $accounts = LedgerEntry::whereBetween('entry_date', [$fromDate, $toDate])
            ->select(
                'account_type',
                DB::raw('SUM(debit)  as total_debit'),
                DB::raw('SUM(credit) as total_credit'),
                DB::raw('COUNT(*)    as entries'),
                DB::raw('MAX(entry_date) as last_activity')
            )
            ->groupBy('account_type')
            ->orderBy('account_type')
            ->get()
            ->map(function ($row) {
                $row->net       = $row->total_credit - $row->total_debit;
                $row->label     = LedgerEntry::ACCOUNT_LABELS[$row->account_type] ?? ucfirst($row->account_type);
                return $row;
            });

        $totalIncome  = $accounts->whereIn('account_type', ['sales', 'cash_in', 'accounts_receivable'])->sum('total_credit');
        $totalExpense = $accounts->whereIn('account_type', ['purchases', 'expenses', 'cash_out', 'refunds', 'payroll'])->sum('total_debit');
        $netProfit    = $totalIncome - $totalExpense;

        return view('admin.ledger.accounts', compact(
            'accounts', 'fromDate', 'toDate',
            'totalIncome', 'totalExpense', 'netProfit'
        ));
    }

    /**
     * Export ledger to CSV
     */
    public function export(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $entries = LedgerEntry::whereBetween('entry_date', [$fromDate, $toDate])
            ->when($request->filled('account_type'),     fn($q) => $q->where('account_type', $request->account_type))
            ->when($request->filled('transaction_type'), fn($q) => $q->where('transaction_type', $request->transaction_type))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $filename = "ledger_{$fromDate}_to_{$toDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Entry #', 'Date', 'Account Type', 'Transaction Type',
                'Reference #', 'Description', 'Party', 'Payment Method',
                'Debit (Rs)', 'Credit (Rs)', 'Net (Rs)',
            ]);

            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->entry_number,
                    $e->entry_date->format('d-M-Y'),
                    LedgerEntry::ACCOUNT_LABELS[$e->account_type] ?? $e->account_type,
                    LedgerEntry::TRANSACTION_LABELS[$e->transaction_type] ?? $e->transaction_type,
                    $e->reference_number,
                    $e->description,
                    $e->party_name,
                    $e->payment_method,
                    number_format($e->debit, 2),
                    number_format($e->credit, 2),
                    number_format($e->credit - $e->debit, 2),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}