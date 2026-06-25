@extends('layouts.app')

@section('title', 'Database Backup')
@section('page-title', 'Database Backup')

@section('breadcrumb')
    <li class="breadcrumb-item active">Database Backup</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-folder text-primary-custom"></i>
                <div>
                    <h6 class="card-title">Backup Location</h6>
                    <p class="card-subtitle">Choose where backup files are stored on the server</p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('backup.update-path') }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-600">Folder Path</label>
                            <input type="text" name="backup_path" class="form-control"
                                   value="{{ $currentPath }}" required
                                   placeholder="e.g. D:\Backups or /var/backups">
                            <div class="form-text">Enter an absolute path to an existing or new folder.</div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-1"></i> Update Path
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-cloud-arrow-down text-primary-custom"></i>
                <div>
                    <h6 class="card-title">Export Database</h6>
                    <p class="card-subtitle">Create a full database backup (mysqldump &rarr; zip)</p>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <p class="mb-1">Click the button to create a complete backup of your database.</p>
                        <small class="text-muted">The backup includes all tables, views, stored procedures, and triggers.</small>
                    </div>
                    <form action="{{ route('backup.create') }}" method="POST" id="createBackupForm">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-arrow-down me-1"></i> Create Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-cloud-arrow-up text-primary-custom"></i>
                <div>
                    <h6 class="card-title">Import Database</h6>
                    <p class="card-subtitle">Restore from a previous backup (.sql or .zip)</p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('backup.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-600">Upload Backup File</label>
                            <input type="file" name="backup_file" class="form-control" accept=".zip,.sql" required>
                            <div class="form-text">Accepted formats: .sql or .zip (containing a .sql file)</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-warning w-100" onclick="return confirm('This will OVERWRITE your entire database. All current data will be lost. Are you sure?')">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Import &amp; Restore
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-archive text-primary-custom"></i>
                <div>
                    <h6 class="card-title">Backup History</h6>
                    <p class="card-subtitle">Download or delete previously created backups</p>
                </div>
            </div>
            <div class="card-body">
                @if(count($files) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Date Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($files as $i => $file)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $file->name }}</code></td>
                                        <td>{{ $file->size_formatted }}</td>
                                        <td>{{ $file->date }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('backup.download', $file->name) }}" class="btn btn-sm btn-success">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                            <form action="{{ route('backup.destroy', $file->name) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this backup permanently?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-archive text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No backups yet. Create your first backup above.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(session('download'))
        window.location.href = '{{ route('backup.download', session('download')) }}';
    @endif

    document.getElementById('createBackupForm')?.addEventListener('submit', function () {
        showLoading('Creating database backup...');
    });
</script>
@endpush
