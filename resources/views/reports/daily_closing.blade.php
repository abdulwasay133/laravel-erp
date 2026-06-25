@extends('layouts.app')

@section('title', 'Daily Closing')
@section('page-title', 'Daily Closing')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Reports</a></li>
    <li class="breadcrumb-item active">Daily Closing</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">Daily Closing</h6>
            <p class="card-subtitle">Review cash position and close the day.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('closing-report.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-table me-1"></i> Closing Report
            </a>
            <a href="{{ route('today-report.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar-day me-1"></i> Today Report
            </a>
        </div>
    </div>
    <div class="card-body">
        <form id="closingDateForm" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Closing Date</label>
                <input type="date" name="date" id="closingDate" class="form-control" value="{{ $date->format('Y-m-d') }}" />
            </div>
            <div class="col-md-4">
                <button type="button" id="loadBtn" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Load
                </button>
            </div>
            <div class="col-md-4 text-md-end">
                <span id="closingStatus" class="badge {{ $existingClosing ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $existingClosing ? 'Closed' : 'Not Closed' }}
                </span>
                <small id="closedInfo" class="d-block text-muted mt-1">
                    @if($existingClosing)
                        Closed by {{ $existingClosing->closedByUser?->name ?? 'Unknown' }}
                        on {{ $existingClosing->created_at->format('d M Y H:i') }}
                    @endif
                </small>
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Last Day Closing</div>
                    <div class="fs-4 fw-semibold" id="lastDayClosing">{{ number_format($figures['last_day_closing'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100 border-success">
                    <div class="text-muted small">Receive</div>
                    <div class="fs-4 fw-semibold text-success" id="receiveAmount">{{ number_format($figures['receive'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100 border-danger">
                    <div class="text-muted small">Payment</div>
                    <div class="fs-4 fw-semibold text-danger" id="paymentAmount">{{ number_format($figures['payment'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100 bg-light">
                    <div class="text-muted small">Balance</div>
                    <div class="fs-4 fw-semibold" id="balanceAmount">{{ number_format($figures['balance'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" id="dayCloseBtn" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> Day Close
            </button>
            <button type="button" id="printBtn" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function formatAmount(value) {
        return parseFloat(value || 0).toFixed(2);
    }

    function updateFigures(figures) {
        $('#lastDayClosing').text(formatAmount(figures.last_day_closing));
        $('#receiveAmount').text(formatAmount(figures.receive));
        $('#paymentAmount').text(formatAmount(figures.payment));
        $('#balanceAmount').text(formatAmount(figures.balance));
    }

    function updateStatus(isClosed, closedBy, closedAt) {
        const badge = $('#closingStatus');
        if (isClosed) {
            badge.removeClass('bg-warning text-dark').addClass('bg-success').text('Closed');
            $('#closedInfo').text('Closed by ' + (closedBy || 'Unknown') + ' on ' + (closedAt || ''));
        } else {
            badge.removeClass('bg-success').addClass('bg-warning text-dark').text('Not Closed');
            $('#closedInfo').text('');
        }
    }

    function loadClosingData() {
        const date = $('#closingDate').val();
        if (!date) {
            Swal.fire({ icon: 'warning', title: 'Date Required', text: 'Please select a closing date.', confirmButtonColor: '#0d6efd' });
            return;
        }

        $.get('{{ route('daily-closing.index') }}', { date: date, ajax: 1 })
            .done(function (response) {
                updateFigures(response.figures);
                updateStatus(response.is_closed, response.closed_by, response.closed_at);
            })
            .fail(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Unable to load closing data.', confirmButtonColor: '#0d6efd' });
            });
    }

    $('#loadBtn').on('click', loadClosingData);

    $('#dayCloseBtn').on('click', function () {
        const date = $('#closingDate').val();
        if (!date) {
            Swal.fire({ icon: 'warning', title: 'Date Required', text: 'Please select a closing date.', confirmButtonColor: '#0d6efd' });
            return;
        }

        Swal.fire({
            title: 'Close the day for ' + date + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Close',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#198754'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post('{{ route('daily-closing.close') }}', { closing_date: date })
                .done(function (response) {
                    updateFigures(response.figures);
                    updateStatus(true, '{{ auth()->user()->name }}', new Date().toLocaleString());
                    Swal.fire({ icon: 'success', title: 'Closed!', text: response.message, timer: 2000, showConfirmButton: false });
                })
                .fail(function (xhr) {
                    const message = xhr.responseJSON?.message || 'Unable to close the day.';
                    Swal.fire({ icon: 'error', title: 'Error', text: message, confirmButtonColor: '#0d6efd' });
                });
        });
    });

    $('#printBtn').on('click', function () {
        const date = $('#closingDate').val();
        if (!date) {
            Swal.fire({ icon: 'warning', title: 'Date Required', text: 'Please select a closing date.', confirmButtonColor: '#0d6efd' });
            return;
        }
        const url = '{{ route('daily-closing.print') }}' + '?date=' + date;
        var iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.top = '-10000px';
        iframe.style.left = '-10000px';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);
        iframe.onload = function () {
            setTimeout(function () {
                iframe.contentWindow.print();
                setTimeout(function () {
                    document.body.removeChild(iframe);
                }, 500);
            }, 500);
        };
        iframe.src = url;
    });
});
</script>
@endpush
