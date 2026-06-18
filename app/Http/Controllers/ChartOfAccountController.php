<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $accounts = ChartOfAccount::with('parent')->orderBy('code')->get();

            return DataTables::of($accounts)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $row->level);
                    $treeLine = $row->level > 0 ? '<span class="tree-line">' : '';
                    $treeLineEnd = $row->level > 0 ? '</span>' : '';
                    $connector = $row->level > 0 ? '└─ ' : '';
                    return $indent . $treeLine . $connector . $row->name . $treeLineEnd;
                })
                ->addColumn('opening_balance', function ($row) {
                    return number_format($row->opening_balance, 2, '.', ',');
                })
                ->addColumn('balance', function ($row) {
                    return number_format($row->current_balance, 2, '.', ',');
                })
                ->addColumn('action', function ($row) {
                    $actions = '<a href="' . route('chart-of-accounts.show', $row->id) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>';
                    $actions .= '<a href="' . route('chart-of-accounts.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                    $actions .= '<button class="btn btn-sm btn-outline-danger delete-account" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    return $actions;
                })
                ->rawColumns(['name', 'action'])
                ->make(true);
        }

        return view('chart-of-accounts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentAccounts = ChartOfAccount::roots()->active()->get();
        $types = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'income' => 'Income',
            'expense' => 'Expense',
        ];

        $subtypes = [
            'current' => 'Current',
            'non_current' => 'Non-Current',
            'current_liability' => 'Current Liability',
            'non_current_liability' => 'Non-Current Liability',
            'current_asset' => 'Current Asset',
            'non_current_asset' => 'Non-Current Asset',
            'operating' => 'Operating',
            'non_operating' => 'Non-Operating',
            'revenue' => 'Revenue',
            'capital' => 'Capital',
            'retained_earnings' => 'Retained Earnings',
        ];

        return view('chart-of-accounts.create', compact('parentAccounts', 'types', 'subtypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'subtype' => 'nullable|in:current,non_current,current_liability,non_current_liability,current_asset,non_current_asset,operating,non_operating,revenue,capital,retained_earnings',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $account = new ChartOfAccount();
        $account->code = $validated['code'];
        $account->name = $validated['name'];
        $account->type = $validated['type'];
        $account->subtype = $validated['subtype'] ?? null;
        $account->parent_id = $validated['parent_id'] ?? null;
        $account->opening_balance = $validated['opening_balance'];
        $account->current_balance = $validated['opening_balance'];
        $account->is_active = $request->has('is_active');
        $account->description = $validated['description'] ?? null;

        // Set level based on parent
        if ($account->parent_id) {
            $parent = ChartOfAccount::find($account->parent_id);
            $account->level = $parent ? $parent->level + 1 : 0;
        } else {
            $account->level = 0;
        }

        $account->save();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Chart of Account created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $account = ChartOfAccount::with('parent', 'children')->findOrFail($id);
        return view('chart-of-accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $parentAccounts = ChartOfAccount::roots()->active()->where('id', '!=', $id)->get();
        $types = [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'income' => 'Income',
            'expense' => 'Expense',
        ];

        $subtypes = [
            'current' => 'Current',
            'non_current' => 'Non-Current',
            'current_liability' => 'Current Liability',
            'non_current_liability' => 'Non-Current Liability',
            'current_asset' => 'Current Asset',
            'non_current_asset' => 'Non-Current Asset',
            'operating' => 'Operating',
            'non_operating' => 'Non-Operating',
            'revenue' => 'Revenue',
            'capital' => 'Capital',
            'retained_earnings' => 'Retained Earnings',
        ];

        return view('chart-of-accounts.edit', compact('account', 'parentAccounts', 'types', 'subtypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:chart_of_accounts,code,' . $id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'subtype' => 'nullable|in:current,non_current,current_liability,non_current_liability,current_asset,non_current_asset,operating,non_operating,revenue,capital,retained_earnings',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $account->code = $validated['code'];
        $account->name = $validated['name'];
        $account->type = $validated['type'];
        $account->subtype = $validated['subtype'] ?? null;
        $account->parent_id = $validated['parent_id'] ?? null;
        $account->opening_balance = $validated['opening_balance'];
        $account->is_active = $request->has('is_active');
        $account->description = $validated['description'] ?? null;

        // Recalculate level based on parent
        if ($account->parent_id) {
            $parent = ChartOfAccount::find($account->parent_id);
            $account->level = $parent ? $parent->level + 1 : 0;
        } else {
            $account->level = 0;
        }

        $account->save();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Chart of Account updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);

        // Check if account has children
        if ($account->children()->count() > 0) {
            return back()->with('error', 'Cannot delete account with child accounts. Please delete or reassign child accounts first.');
        }

        $account->delete();

        return back()->with('success', 'Chart of Account deleted successfully!');
    }
}
