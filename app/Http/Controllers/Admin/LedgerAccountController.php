<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\LedgerAccountEntry;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerAccountController extends Controller
{
    // ─── List all ledger accounts ────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = LedgerAccount::withCount('entries')
            ->withSum('entries as total_debit_sum', DB::raw('CASE WHEN debit > 0 THEN debit ELSE 0 END'))
            ->withSum('entries as total_credit_sum', DB::raw('CASE WHEN credit > 0 THEN credit ELSE 0 END'));

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('account_code', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        $accounts = $query->orderBy('type')->orderBy('name')->get();

        // Summary per type
        $summary = [
            'expense'   => (clone $query)->where('type', 'expense')->get(),
            'income'    => (clone $query)->where('type', 'income')->get(),
            'asset'     => (clone $query)->where('type', 'asset')->get(),
            'liability' => (clone $query)->where('type', 'liability')->get(),
        ];

        return view('admin.ledger-accounts.index', compact('accounts', 'summary'));
    }

    // ─── Show create form ────────────────────────────────────────────────────
    public function create()
    {
        $accountCode = LedgerAccount::generateAccountCode();
        return view('admin.ledger-accounts.create', compact('accountCode'));
    }

    // ─── Store new ledger account ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:expense,income,asset,liability',
            'category'        => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        LedgerAccount::create([
            'account_code'    => LedgerAccount::generateAccountCode(),
            'name'            => $request->name,
            'type'            => $request->type,
            'category'        => $request->category,
            'description'     => $request->description,
            'opening_balance' => $request->opening_balance ?? 0,
            'is_active'       => true,
            'created_by'      => auth()->id(),
        ]);

        return redirect()->route('admin.ledger-accounts.index')
            ->with('success', "Ledger account '{$request->name}' created successfully!");
    }

    // ─── Edit form ───────────────────────────────────────────────────────────
    public function edit(LedgerAccount $ledgerAccount)
    {
        return view('admin.ledger-accounts.edit', compact('ledgerAccount'));
    }

    // ─── Update ──────────────────────────────────────────────────────────────
    public function update(Request $request, LedgerAccount $ledgerAccount)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:expense,income,asset,liability',
            'category'        => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active'       => 'nullable|boolean',
        ]);

        $ledgerAccount->update([
            'name'            => $request->name,
            'type'            => $request->type,
            'category'        => $request->category,
            'description'     => $request->description,
            'opening_balance' => $request->opening_balance ?? 0,
            'is_active'       => $request->has('is_active'),
        ]);

        return redirect()->route('admin.ledger-accounts.show', $ledgerAccount)
            ->with('success', 'Ledger account updated successfully!');
    }

    // ─── Delete ──────────────────────────────────────────────────────────────
    public function destroy(LedgerAccount $ledgerAccount)
    {
        if ($ledgerAccount->entries()->count() > 0) {
            return back()->with('error', 'Cannot delete — this ledger has existing entries. Deactivate it instead.');
        }

        $ledgerAccount->delete();
        return redirect()->route('admin.ledger-accounts.index')
            ->with('success', 'Ledger account deleted.');
    }

    // ─── Show ledger detail with all entries ─────────────────────────────────
    public function show(Request $request, LedgerAccount $ledgerAccount)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        $entries = LedgerAccountEntry::where('ledger_account_id', $ledgerAccount->id)
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(30);

        // Running balance calculation
        $openingBalance = $ledgerAccount->opening_balance;
        $priorDebit  = LedgerAccountEntry::where('ledger_account_id', $ledgerAccount->id)
            ->where('entry_date', '<', $fromDate)->sum('debit');
        $priorCredit = LedgerAccountEntry::where('ledger_account_id', $ledgerAccount->id)
            ->where('entry_date', '<', $fromDate)->sum('credit');

        if (in_array($ledgerAccount->type, ['income', 'liability'])) {
            $openingRunning = $openingBalance + $priorCredit - $priorDebit;
        } else {
            $openingRunning = $openingBalance + $priorDebit - $priorCredit;
        }

        // Period totals
        $periodDebit  = $entries->sum('debit');
        $periodCredit = $entries->sum('credit');

        // CSV Export
        if ($request->has('export')) {
            return $this->exportCsv($ledgerAccount, $fromDate, $toDate);
        }

        return view('admin.ledger-accounts.show', compact(
            'ledgerAccount', 'entries', 'fromDate', 'toDate',
            'openingRunning', 'periodDebit', 'periodCredit'
        ));
    }

    // ─── Add manual journal entry ─────────────────────────────────────────────
    public function addEntry(Request $request, LedgerAccount $ledgerAccount)
    {
        $request->validate([
            'entry_date'     => 'required|date',
            'description'    => 'required|string|max:500',
            'entry_type'     => 'required|in:debit,credit',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        LedgerAccountEntry::create([
            'entry_number'       => LedgerAccountEntry::generateEntryNumber(),
            'ledger_account_id'  => $ledgerAccount->id,
            'entry_date'         => $request->entry_date,
            'description'        => $request->description,
            'debit'              => $request->entry_type === 'debit'  ? $request->amount : 0,
            'credit'             => $request->entry_type === 'credit' ? $request->amount : 0,
            'reference_type'     => 'manual',
            'reference_number'   => 'MAN-' . strtoupper(uniqid()),
            'payment_method'     => $request->payment_method,
            'notes'              => $request->notes,
            'created_by'         => auth()->id(),
        ]);

        return back()->with('success', 'Entry added to ledger successfully!');
    }

    // ─── Toggle active status ────────────────────────────────────────────────
    public function toggleActive(LedgerAccount $ledgerAccount)
    {
        $ledgerAccount->update(['is_active' => !$ledgerAccount->is_active]);
        $status = $ledgerAccount->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Ledger account {$status}.");
    }

    // ─── CSV Export ──────────────────────────────────────────────────────────
    private function exportCsv(LedgerAccount $account, string $from, string $to)
    {
        $entries = LedgerAccountEntry::where('ledger_account_id', $account->id)
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('entry_date')
            ->get();

        $filename = "ledger_{$account->account_code}_{$from}_to_{$to}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($entries, $account) {
            $handle  = fopen('php://output', 'w');
            fputcsv($handle, ['Entry #', 'Date', 'Description', 'Debit (Rs)', 'Credit (Rs)', 'Method', 'Notes']);

            $runningBalance = $account->opening_balance;

            foreach ($entries as $e) {
                if (in_array($account->type, ['income', 'liability'])) {
                    $runningBalance += $e->credit - $e->debit;
                } else {
                    $runningBalance += $e->debit - $e->credit;
                }

                fputcsv($handle, [
                    $e->entry_number,
                    $e->entry_date->format('d-M-Y'),
                    $e->description,
                    $e->debit > 0 ? number_format($e->debit, 2) : '',
                    $e->credit > 0 ? number_format($e->credit, 2) : '',
                    $e->payment_method ?? '',
                    $e->notes ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}