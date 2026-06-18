<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::getValue('company_name', 'ERP System') }}</title>

    <!-- Google Fonts: Inter + Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables CSS (load ONCE) -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    
    <!-- flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Your custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatable_custom.css') }}" rel="stylesheet">

    <style>
        :root {
            --fp-primary: #85D1DB;
            --fp-primary-light: rgba(133, 209, 219, 0.15);
        }
        .flatpickr-calendar {
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            font-family: 'Inter', sans-serif;
        }
        .flatpickr-day.today { border-color: var(--fp-primary); }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange,
        .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange,
        .flatpickr-day:focus, .flatpickr-day:hover {
            background: var(--fp-primary) !important;
            border-color: var(--fp-primary) !important;
            color: #1E3A4C !important;
        }
        .flatpickr-day.inRange {
            background: var(--fp-primary-light);
            border-color: transparent;
            box-shadow: -5px 0 0 var(--fp-primary-light),5px 0 0 var(--fp-primary-light);
        }
        .flatpickr-months .flatpickr-month { background: transparent; }
        .flatpickr-current-month .flatpickr-monthDropdown-months { font-weight: 600; }
        .flatpickr-current-month input.cur-year { font-weight: 600; }
        .flatpickr-months .flatpickr-prev-month svg,
        .flatpickr-months .flatpickr-next-month svg { fill: #85D1DB; }
        .flatpickr-weekday { color: #6B7280; font-weight: 600; }
        .numInputWrapper span.arrowUp, .numInputWrapper span.arrowDown { display: none; }

        .date-range-group {
            display: flex;
            align-items: center;
            border: 1.5px solid #E5E7EB;
            border-radius: 8px;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .date-range-group:focus-within {
            border-color: #85D1DB;
            box-shadow: 0 0 0 3px rgba(133, 209, 219, 0.12);
        }
        .date-range-group .form-control,
        .date-range-group .flatpickr-input {
            border: none;
            background: transparent;
            padding: 0.4rem 0.6rem;
            font-size: 0.875rem;
        }
        .date-range-group .form-control:focus,
        .date-range-group .flatpickr-input:focus { box-shadow: none; }
        .range-sep {
            flex-shrink: 0;
            color: #9CA3AF;
            font-weight: 600;
            font-size: 14px;
            padding: 0 2px;
            user-select: none;
        }

        /* ── Select2 theme ── */
        .select2-container--bootstrap-5 .select2-selection {
            border-color: #E5E7EB;
            border-radius: 8px;
            min-height: 39px;
            transition: all 0.2s ease;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #85D1DB;
            box-shadow: 0 0 0 3px rgba(133, 209, 219, 0.12);
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: #E5E7EB;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .select2-container--bootstrap-5 .select2-results__option--selected {
            background: rgba(133, 209, 219, 0.08);
            color: #1E3A4C;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background: #85D1DB;
            color: #1E3A4C;
        }
        .select2-container--bootstrap-5 .select2-search__field:focus {
            border-color: #85D1DB;
            box-shadow: 0 0 0 3px rgba(133, 209, 219, 0.12);
        }
    </style>


    @stack('styles')
</head>
<body>
<x-sweet-alert />
<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ══ SIDEBAR ══════════════════════════════════════════ -->
@include('layouts.partials.sidebar')

<!-- ══ TOPBAR ════════════════════════════════════════════ -->
@include('layouts.partials.topbar')

<!-- ══ MAIN CONTENT ══════════════════════════════════════ -->
<main id="main-content">



    @yield('content')

    

</main>

<!-- ══ SCRIPTS (load ONCE, correct order) ══════════════════ -->

<!-- 1. jQuery FIRST -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- 2. Bootstrap JS (needs jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. DataTables core -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<!-- 4. DataTables Buttons extension -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

<!-- 5. Export dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- 6. Button types -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- 7. Layout JS -->
<script>
    // Sidebar toggle (mobile)
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');

    document.getElementById('sidebarToggle').addEventListener('click', () => {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });

    // Submenu toggle
    document.querySelectorAll('.has-sub > .nav-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            link.closest('.has-sub').classList.toggle('open');
        });
    });

    // flatpickr — two linked pickers (start / end)
    $('.date-range-group').each(function () {
        var $g = $(this);
        var startHidden = $g.find('#startDate')[0];
        var endHidden   = $g.find('#endDate')[0];
        if (!startHidden || !endHidden) return;
        var $startEl = $g.find('.flatpickr-start');
        var $endEl   = $g.find('.flatpickr-end');
        if (!$startEl.length || !$endEl.length) return;

        var startFp = flatpickr($startEl[0], {
            dateFormat: 'Y-m-d',
            altFormat: 'M j, Y',
            altInput: true,
            altInputClass: 'form-control bg-transparent',
            maxDate: endHidden.value || null,
            onChange: function (dates) {
                startHidden.value = dates.length ? startFp.formatDate(dates[0], 'Y-m-d') : '';
                endFp.set('minDate', dates.length ? dates[0] : null);
            },
        });
        var endFp = flatpickr($endEl[0], {
            dateFormat: 'Y-m-d',
            altFormat: 'M j, Y',
            altInput: true,
            altInputClass: 'form-control bg-transparent',
            minDate: startHidden.value || null,
            onChange: function (dates) {
                endHidden.value = dates.length ? endFp.formatDate(dates[0], 'Y-m-d') : '';
                startFp.set('maxDate', dates.length ? dates[0] : null);
            },
        });
    });

    // Select2 — searchable dropdowns
    function initSelect2($el) {
        if ($el.data('select2')) $el.select2('destroy');
        var opts = {
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: $el.find('option:first').text() || 'Select...',
            allowClear: true,
            minimumResultsForSearch: 0,
            dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $(document.body),
        };
        $el.select2(opts);
    }
    $('select.form-select:not([multiple]), select.product-select:not([multiple]), select.batch-select:not([multiple])').each(function () { initSelect2($(this)); });
    // Re-init after DataTables draws
    $(document).on('draw.dt', function () {
        setTimeout(function () {
            $('select.form-select:not([multiple]):not(.select2-hidden-accessible), select.product-select:not([multiple]):not(.select2-hidden-accessible), select.batch-select:not([multiple]):not(.select2-hidden-accessible)').each(function () { initSelect2($(this)); });
        }, 50);
    });
    // Auto-init dynamically added rows (Add Item)
    var selObserver = new MutationObserver(function (muts) {
        muts.forEach(function (m) {
            m.addedNodes.forEach(function (n) {
                if (n.nodeType !== 1) return;
                $(n).find('select.product-select:not(.select2-hidden-accessible), select.batch-select:not(.select2-hidden-accessible)').add(n).filter('select.product-select:not(.select2-hidden-accessible), select.batch-select:not(.select2-hidden-accessible)').each(function () { initSelect2($(this)); });
            });
        });
    });
    selObserver.observe(document.body, { childList: true, subtree: true });
</script>

<!-- 8. Page-specific scripts pushed here -->
@stack('scripts')

</body>
</html>