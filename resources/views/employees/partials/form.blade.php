@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person-badge text-primary-custom"></i>
        <div>
            <h6 class="card-title">Personal Information</h6>
            <p class="card-subtitle">Employee personal details</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-600">Employee Code <span class="text-danger">*</span></label>
                <input type="text" name="employee_code" class="form-control" required
                       value="{{ old('employee_code', $employee->employee_code ?? '') }}" placeholder="EMP-001">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" required
                       value="{{ old('first_name', $employee->first_name ?? '') }}" placeholder="John">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" required
                       value="{{ old('last_name', $employee->last_name ?? '') }}" placeholder="Doe">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email', $employee->email ?? '') }}" placeholder="john@example.com">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Phone</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ old('phone', $employee->phone ?? '') }}" placeholder="+92 300 1234567">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Department</label>
                <input type="text" name="department" class="form-control"
                       value="{{ old('department', $employee->department ?? '') }}" placeholder="IT, Sales, etc.">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Designation</label>
                <input type="text" name="designation" class="form-control"
                       value="{{ old('designation', $employee->designation ?? '') }}" placeholder="Manager, Developer, etc.">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Salary Amount <span class="text-danger">*</span></label>
                <input type="number" name="salary_amount" step="0.01" min="0" class="form-control" required
                       value="{{ old('salary_amount', $employee->salary_amount ?? '') }}" placeholder="50000">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Joining Date <span class="text-danger">*</span></label>
                <input type="date" name="joining_date" class="form-control" required
                       value="{{ old('joining_date', isset($employee) ? $employee->joining_date->format('Y-m-d') : date('Y-m-d')) }}">
            </div>
            <div class="col-12">
                <label class="form-label fw-600">Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Employee address...">{{ old('address', $employee->address ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-cash-coin text-primary-custom"></i>
        <div>
            <h6 class="card-title">Commission Settings</h6>
            <p class="card-subtitle">Order booker commission configuration</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="form-check form-switch mt-4">
                    <input type="checkbox" name="is_order_booker" value="1" class="form-check-input" id="isOrderBooker"
                           {{ old('is_order_booker', $employee->is_order_booker ?? false) ? 'checked' : '' }}
                           onchange="toggleCommissionFields(this)">
                    <label class="form-check-label fw-600" for="isOrderBooker">Order Booker</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Commission Type</label>
                <select name="commission_type" class="form-select" id="commissionType">
                    <option value="fixed_percent" {{ old('commission_type', $employee->commission_type ?? '') === 'fixed_percent' ? 'selected' : '' }}>Fixed Percentage</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Commission Rate (%)</label>
                <input type="number" name="commission_rate" step="0.01" min="0" class="form-control" id="commissionRate"
                       value="{{ old('commission_rate', $employee->commission_rate ?? '') }}" placeholder="5.00">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-600">Territory</label>
                <input type="text" name="territory" class="form-control" id="territory"
                       value="{{ old('territory', $employee->territory ?? '') }}" placeholder="e.g. North Region">
            </div>
        </div>
    </div>
</div>

<div class="card d-flex flex-row justify-content-between align-items-center p-3">
    <div>
        <label class="toggle-switch mb-0">
            <input type="checkbox" name="status" value="1" {{ old('status', $employee->status ?? true) ? 'checked' : '' }}>
            <span class="toggle-switch__slider"></span>
            <span class="toggle-switch__label">Active</span>
        </label>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ isset($employee) ? 'Update Employee' : 'Save Employee' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function toggleCommissionFields(checkbox) {
    const checked = checkbox.checked;
    document.getElementById('commissionType').disabled = !checked;
    document.getElementById('commissionRate').disabled = !checked;
    document.getElementById('territory').disabled = !checked;
}
document.addEventListener('DOMContentLoaded', function () {
    const cb = document.getElementById('isOrderBooker');
    if (cb) toggleCommissionFields(cb);
});
</script>
@endpush
