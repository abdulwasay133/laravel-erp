<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-receipt-cutoff text-primary-custom"></i>
        <div>
            <h6 class="card-title">Expense Details</h6>
            <p class="card-subtitle">Record an expense and deduct from cash or bank</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-600">Voucher Number <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" name="voucher_no" id="voucher_no" class="form-control"
                           value="{{ old('voucher_no', $expense->voucher_no ?? '') }}" readonly>
                    @unless(isset($expense))
                        <button type="button" class="btn btn-outline-secondary" id="generateBtn">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    @endunless
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Expense Date <span class="text-danger">*</span></label>
                <input type="date" name="expense_date" class="form-control" required
                       value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Expense Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required
                       value="{{ old('title', $expense->title ?? '') }}" placeholder="e.g. Office Rent">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Expense Account <span class="text-danger">*</span></label>
                <select name="chart_of_account_id" class="form-select" required>
                    <option value="">-- Select Expense Account --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}"
                            {{ old('chart_of_account_id', $expense->chart_of_account_id ?? '') == $account->id ? 'selected' : '' }}>
                            {{ $account->code }} - {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Amount <span class="text-danger">*</span></label>
                <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required
                       value="{{ old('amount', $expense->amount ?? '') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" id="paymentMethod" class="form-select" required>
                    <option value="">-- Select Method --</option>
                    <option value="cash" {{ old('payment_method', $expense->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ old('payment_method', $expense->payment_method ?? '') == 'bank' ? 'selected' : '' }}>Bank</option>
                </select>
            </div>

            <div class="col-md-6" id="bankAccountDiv" style="display:none;">
                <label class="form-label fw-600">Bank Account <span class="text-danger">*</span></label>
                <select name="bank_account_id" class="form-select">
                    <option value="">-- Select Bank Account --</option>
                    @foreach($bankAccounts as $bank)
                        <option value="{{ $bank->id }}"
                            {{ old('bank_account_id', $expense->bank_account_id ?? '') == $bank->id ? 'selected' : '' }}>
                            {{ $bank->bank_name }} - {{ $bank->account_title }} ({{ $bank->account_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Reference</label>
                <input type="text" name="reference" class="form-control"
                       value="{{ old('reference', $expense->reference ?? '') }}" placeholder="Receipt no, bill no, etc.">
            </div>

            <div class="col-12">
                <label class="form-label fw-600">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Additional expense details...">{{ old('description', $expense->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body d-flex justify-content-between">
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ isset($expense) ? 'Update Expense' : 'Save Expense' }}
        </button>
    </div>
</div>
