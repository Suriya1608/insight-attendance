@extends('layouts.app')

@section('title', 'Documents – ' . $employee->name)

@push('styles')
<style>
    /* ── Page header ── */
    .page-header {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 28px; flex-wrap: wrap;
    }
    .page-header .header-icon {
        width: 42px; height: 42px;
        background: var(--primary-subtle);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: var(--primary); font-size: 22px; flex-shrink: 0;
    }
    .page-header h1 { font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0; }
    .page-header p  { font-size: .82rem; color: var(--text-secondary); margin: 0; }
    .header-actions { margin-left: auto; display: flex; gap: 10px; flex-shrink: 0; }

    /* ── Summary bar ── */
    .doc-summary {
        display: flex; gap: 12px; flex-wrap: wrap;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-md); padding: 14px 20px;
        margin-bottom: 24px;
    }
    .doc-summary-item {
        display: flex; align-items: center; gap: 8px;
        font-size: .82rem; color: var(--text-secondary);
    }
    .doc-summary-item strong { color: var(--text-main); font-weight: 700; }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .dot-ok      { background: #10b981; }
    .dot-missing { background: #f43f5e; }
    .dot-opt     { background: #94a3b8; }

    /* ── Missing banner ── */
    .missing-banner {
        background: rgba(244,63,94,.06);
        border: 1px solid rgba(244,63,94,.2);
        border-radius: var(--radius-md);
        padding: 12px 18px;
        margin-bottom: 20px;
        display: flex; align-items: flex-start; gap: 10px;
        font-size: .83rem;
    }
    .missing-banner .material-symbols-outlined { color: #f43f5e; font-size: 18px; flex-shrink: 0; margin-top: 1px; }

    /* ── Document grid ── */
    .doc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
    }
    @media (max-width: 600px) { .doc-grid { grid-template-columns: 1fr; } }

    /* ── Document card ── */
    .doc-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex; flex-direction: column;
        transition: box-shadow .15s;
    }
    .doc-card:hover { box-shadow: var(--shadow-md); }

    .doc-card-header {
        padding: 14px 16px 10px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: flex-start; gap: 12px;
    }
    .doc-type-icon {
        width: 38px; height: 38px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .doc-type-icon.uploaded { background: rgba(19,127,236,.1);  color: var(--primary); }
    .doc-type-icon.empty    { background: var(--bg-light);       color: var(--text-muted); }

    .doc-card-title { flex: 1; min-width: 0; }
    .doc-card-title h6 {
        font-size: .87rem; font-weight: 700; color: var(--text-main);
        margin: 0 0 4px; line-height: 1.3;
    }

    .badge-mandatory {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .68rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; border-radius: 4px;
        padding: 2px 7px;
    }
    .badge-mandatory.req  { background: rgba(244,63,94,.1); color: #e11d48; }
    .badge-mandatory.opt  { background: rgba(148,163,184,.1); color: #64748b; }

    /* ── Uploaded state ── */
    .doc-card-body { padding: 14px 16px; flex: 1; }
    .file-info {
        display: flex; align-items: center; gap: 10px;
        background: var(--bg-light);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 8px 12px;
        margin-bottom: 12px;
    }
    .file-info .ext-badge {
        font-size: .65rem; font-weight: 800; text-transform: uppercase;
        border-radius: 4px; padding: 2px 6px; flex-shrink: 0;
    }
    .ext-badge.pdf  { background: rgba(239,68,68,.12); color: #dc2626; }
    .ext-badge.jpg,
    .ext-badge.jpeg,
    .ext-badge.png  { background: rgba(16,185,129,.12); color: #059669; }
    .file-info .file-name {
        font-size: .82rem; font-weight: 500; color: var(--text-main);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;
    }
    .file-meta {
        display: flex; gap: 14px; flex-wrap: wrap;
        font-size: .75rem; color: var(--text-secondary);
    }
    .file-meta .meta-item { display: flex; align-items: center; gap: 4px; }
    .file-meta .material-symbols-outlined { font-size: 13px; }

    /* ── Action buttons ── */
    .doc-actions {
        display: flex; gap: 6px; flex-wrap: wrap;
        padding: 10px 16px;
        border-top: 1px solid var(--border);
        background: #fafbfc;
    }
    .btn-doc {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: .78rem; font-weight: 600;
        border-radius: 6px; padding: 5px 12px;
        border: 1px solid; cursor: pointer;
        text-decoration: none; transition: all .15s;
    }
    .btn-doc .material-symbols-outlined { font-size: 14px; }
    .btn-doc-view     { background: rgba(19,127,236,.08); border-color: rgba(19,127,236,.2); color: var(--primary); }
    .btn-doc-view:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
    .btn-doc-dl       { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.2); color: #059669; }
    .btn-doc-dl:hover { background: #059669; border-color: #059669; color: #fff; }
    .btn-doc-replace  { background: rgba(245,158,11,.08); border-color: rgba(245,158,11,.2); color: #b45309; }
    .btn-doc-replace:hover { background: #f59e0b; border-color: #f59e0b; color: #fff; }
    .btn-doc-delete   { background: rgba(244,63,94,.08); border-color: rgba(244,63,94,.2); color: #e11d48; }
    .btn-doc-delete:hover { background: #f43f5e; border-color: #f43f5e; color: #fff; }
    .btn-doc-upload   { background: var(--primary); border-color: var(--primary); color: #fff; }
    .btn-doc-upload:hover { background: var(--primary-hover); border-color: var(--primary-hover); color: #fff; }

    /* ── Empty state ── */
    .doc-empty-body {
        padding: 18px 16px;
        text-align: center;
    }
    .doc-empty-body p { font-size: .83rem; color: var(--text-secondary); margin: 0 0 12px; }

    /* ── Upload modal ── */
    .upload-modal .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 16px 20px;
    }
    .upload-modal .modal-title { font-size: .95rem; font-weight: 700; }
    .upload-modal .modal-body  { padding: 20px; }
    .upload-modal .modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); }

    .drop-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius-md);
        padding: 28px 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
    }
    .drop-zone.dragover { border-color: var(--primary); background: var(--primary-subtle); }
    .drop-zone input[type=file] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .drop-zone .dz-icon { font-size: 2rem; color: var(--text-muted); margin-bottom: 8px; }
    .drop-zone .dz-text { font-size: .83rem; color: var(--text-secondary); }
    .drop-zone .dz-text strong { color: var(--primary); }
    .dz-hint { font-size: .75rem; color: var(--text-muted); margin-top: 4px; }
    #file-chosen { font-size: .8rem; color: var(--text-secondary); margin-top: 8px; min-height: 20px; }

    .btn-primary-sm {
        display: inline-flex; align-items: center; gap: 5px;
        background: var(--primary); color: #fff;
        border: none; border-radius: 7px; padding: 7px 18px;
        font-size: .83rem; font-weight: 600; cursor: pointer;
        transition: background .15s;
    }
    .btn-primary-sm:hover { background: var(--primary-hover); }
    .btn-secondary-sm {
        display: inline-flex; align-items: center; gap: 5px;
        background: transparent; color: var(--text-secondary);
        border: 1px solid var(--border); border-radius: 7px; padding: 7px 14px;
        font-size: .83rem; font-weight: 500; cursor: pointer;
        transition: all .15s;
    }
    .btn-secondary-sm:hover { border-color: var(--primary); color: var(--primary); }

    /* ── Preview modal ── */
    #previewFrame { width: 100%; height: 70vh; border: none; border-radius: var(--radius-sm); }
    #previewImg   { max-width: 100%; max-height: 70vh; border-radius: var(--radius-sm); display: block; margin: 0 auto; }

    /* Nav buttons */
    .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: transparent; color: var(--text-secondary);
        border: 1px solid var(--border); border-radius: 8px;
        padding: 8px 16px; font-size: .83rem; font-weight: 500;
        text-decoration: none; transition: all .18s;
    }
    .btn-back:hover { border-color: var(--primary); color: var(--primary); }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-icon">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">folder_shared</span>
    </div>
    <div>
        <h1>Employee Documents</h1>
        <p>{{ $employee->name }} &mdash; {{ $employee->employee_code }}</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.employees.show', $employee) }}" class="btn-back">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
            Back to Profile
        </a>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">check_circle</span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px;font-variation-settings:'FILL' 1">error</span>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Summary bar --}}
@php
    $totalTypes   = count($docTypes);
    $uploadedCount= $documents->count();
    $mandatoryTotal = count(array_filter($docTypes, fn($t) => $t['mandatory']));
    $mandatoryDone  = $documents->filter(fn($d) => $docTypes[$d->document_type]['mandatory'] ?? false)->count();
@endphp
<div class="doc-summary">
    <div class="doc-summary-item">
        <span class="dot dot-ok"></span>
        <strong>{{ $uploadedCount }}</strong>&nbsp;/ {{ $totalTypes }} uploaded
    </div>
    <div class="doc-summary-item" style="margin-left:auto;">
        <span class="dot {{ $mandatoryDone < $mandatoryTotal ? 'dot-missing' : 'dot-ok' }}"></span>
        Mandatory:&nbsp;<strong>{{ $mandatoryDone }} / {{ $mandatoryTotal }}</strong>
    </div>
</div>

{{-- Missing mandatory banner --}}
@if(count($missingMandatory) > 0)
    <div class="missing-banner">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">warning</span>
        <div>
            <strong>Missing mandatory documents:</strong>
            {{ implode(', ', array_map(fn($k) => $docTypes[$k]['label'], $missingMandatory)) }}
        </div>
    </div>
@endif

{{-- Document cards --}}
<div class="doc-grid">
    @foreach($docTypes as $typeKey => $typeDef)
        @php $doc = $documents->get($typeKey); @endphp
        <div class="doc-card">

            {{-- Card header --}}
            <div class="doc-card-header">
                <div class="doc-type-icon {{ $doc ? 'uploaded' : 'empty' }}">
                    <span class="material-symbols-outlined" style="font-size:20px;font-variation-settings:'FILL' 1">
                        {{ $doc ? 'description' : 'draft' }}
                    </span>
                </div>
                <div class="doc-card-title">
                    <h6>{{ $typeDef['label'] }}</h6>
                    <span class="badge-mandatory {{ $typeDef['mandatory'] ? 'req' : 'opt' }}">
                        {{ $typeDef['mandatory'] ? 'Required' : 'Optional' }}
                    </span>
                </div>
                @if($doc)
                    <span title="Uploaded" style="color:#10b981;flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:20px;font-variation-settings:'FILL' 1">check_circle</span>
                    </span>
                @endif
            </div>

            @if($doc)
                {{-- Uploaded state --}}
                <div class="doc-card-body">
                    <div class="file-info">
                        <span class="ext-badge {{ $doc->extension() }}">{{ strtoupper($doc->extension()) }}</span>
                        <span class="file-name" title="{{ $doc->file_name }}">{{ $doc->file_name }}</span>
                    </div>
                    <div class="file-meta">
                        <span class="meta-item">
                            <span class="material-symbols-outlined">calendar_today</span>
                            {{ $doc->created_at->format('d M Y') }}
                        </span>
                        @if($doc->updated_at && $doc->updated_at->ne($doc->created_at))
                            <span class="meta-item">
                                <span class="material-symbols-outlined">update</span>
                                Updated {{ $doc->updated_at->format('d M Y') }}
                            </span>
                        @endif
                        @if($doc->uploader)
                            <span class="meta-item">
                                <span class="material-symbols-outlined">person</span>
                                {{ $doc->uploader->name }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="doc-actions">
                    {{-- View --}}
                    @if($doc->isInlineViewable())
                        <button type="button" class="btn-doc btn-doc-view"
                                onclick="openPreview('{{ route('admin.employees.documents.view', [$employee, $doc]) }}', '{{ $doc->extension() }}', '{{ addslashes($typeDef['label']) }}')">
                            <span class="material-symbols-outlined">visibility</span> View
                        </button>
                    @endif
                    {{-- Download --}}
                    <a href="{{ route('admin.employees.documents.download', [$employee, $doc]) }}"
                       class="btn-doc btn-doc-dl">
                        <span class="material-symbols-outlined">download</span> Download
                    </a>
                    {{-- Replace --}}
                    <button type="button" class="btn-doc btn-doc-replace"
                            onclick="openUpload('{{ $typeKey }}', '{{ addslashes($typeDef['label']) }}', true)">
                        <span class="material-symbols-outlined">upload_file</span> Replace
                    </button>
                    {{-- Delete --}}
                    <button type="button" class="btn-doc btn-doc-delete"
                            onclick="confirmDelete('{{ route('admin.employees.documents.destroy', [$employee, $doc]) }}', '{{ addslashes($typeDef['label']) }}')">
                        <span class="material-symbols-outlined">delete</span> Delete
                    </button>
                </div>

            @else
                {{-- Empty state --}}
                <div class="doc-empty-body">
                    <p>No document uploaded yet.</p>
                </div>
                <div class="doc-actions">
                    <button type="button" class="btn-doc btn-doc-upload"
                            onclick="openUpload('{{ $typeKey }}', '{{ addslashes($typeDef['label']) }}', false)">
                        <span class="material-symbols-outlined">upload</span> Upload
                    </button>
                </div>
            @endif

        </div>
    @endforeach
</div>

{{-- ── Upload Modal ── --}}
<div class="modal fade upload-modal" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border-radius:14px;border:1px solid var(--border);">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST"
                  action="{{ route('admin.employees.documents.store', $employee) }}"
                  enctype="multipart/form-data"
                  id="uploadForm">
                @csrf
                <input type="hidden" name="document_type" id="modalDocType">
                <div class="modal-body">
                    <p id="modalDocLabel" style="font-size:.85rem;font-weight:600;color:var(--text-main);margin-bottom:14px;"></p>

                    {{-- Validation errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger" style="font-size:.82rem;border-radius:8px;padding:10px 14px;">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="drop-zone" id="dropZone">
                        <input type="file" name="document_file" id="docFileInput"
                               accept=".pdf,.jpg,.jpeg,.png"
                               onchange="onFileChosen(this)">
                        <div class="dz-icon">
                            <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">upload_file</span>
                        </div>
                        <div class="dz-text">
                            <strong>Click to browse</strong> or drag & drop
                        </div>
                        <div class="dz-hint">PDF, JPG, PNG &nbsp;·&nbsp; max {{ number_format(config('employee_documents.max_size_kb', 2048) / 1024, 0) }} MB</div>
                    </div>
                    <div id="file-chosen"></div>
                </div>
                <div class="modal-footer" style="justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn-secondary-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-sm" id="uploadSubmitBtn">
                        <span class="material-symbols-outlined" style="font-size:16px;">cloud_upload</span>
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Preview Modal ── --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:1px solid var(--border);">
            <div class="modal-header" style="border-bottom:1px solid var(--border);padding:14px 20px;">
                <h5 class="modal-title" id="previewModalLabel" style="font-size:.95rem;font-weight:700;"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <iframe id="previewFrame" src="" style="display:none;"></iframe>
                <img id="previewImg" src="" alt="" style="display:none;">
            </div>
        </div>
    </div>
</div>

{{-- ── Delete Confirm Modal ── --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:14px;border:1px solid var(--border);">
            <div class="modal-body" style="padding:28px 24px;text-align:center;">
                <div style="width:52px;height:52px;border-radius:50%;background:rgba(244,63,94,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <span class="material-symbols-outlined" style="color:#f43f5e;font-size:26px;font-variation-settings:'FILL' 1">delete</span>
                </div>
                <h5 style="font-size:1rem;font-weight:700;margin-bottom:8px;">Delete Document?</h5>
                <p id="deleteDocLabel" style="font-size:.85rem;color:var(--text-secondary);margin-bottom:20px;"></p>
                @if(config('employee_documents.archive_on_replace', true))
                    <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:20px;">
                        <span class="material-symbols-outlined align-middle" style="font-size:14px;">archive</span>
                        The file will be archived, not permanently deleted.
                    </p>
                @endif
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button type="button" class="btn-secondary-sm" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-doc btn-doc-delete">
                            <span class="material-symbols-outlined">delete</span> Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Upload modal ─────────────────────────────────────────────────────────────
function openUpload(typeKey, typeLabel, isReplace) {
    document.getElementById('modalDocType').value  = typeKey;
    document.getElementById('modalDocLabel').textContent = (isReplace ? 'Replace: ' : 'Upload: ') + typeLabel;
    document.getElementById('uploadSubmitBtn').querySelector('span').nextSibling.textContent = isReplace ? ' Replace' : ' Upload';
    document.getElementById('file-chosen').textContent = '';
    document.getElementById('docFileInput').value = '';

    @if($errors->any())
    // If we have validation errors, pre-fill the type from old input
    @endif

    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function onFileChosen(input) {
    const el = document.getElementById('file-chosen');
    if (input.files.length > 0) {
        const file = input.files[0];
        const kb   = (file.size / 1024).toFixed(0);
        el.innerHTML = '<span class="material-symbols-outlined align-middle" style="font-size:14px;color:var(--primary);">check_circle</span> '
                     + file.name + ' (' + kb + ' KB)';
    } else {
        el.textContent = '';
    }
}

// Drag & drop visual feedback
const dz = document.getElementById('dropZone');
if (dz) {
    dz.addEventListener('dragover',  () => dz.classList.add('dragover'));
    dz.addEventListener('dragleave', () => dz.classList.remove('dragover'));
    dz.addEventListener('drop',      () => dz.classList.remove('dragover'));
}

// ── Preview modal ─────────────────────────────────────────────────────────────
function openPreview(url, ext, label) {
    const frame = document.getElementById('previewFrame');
    const img   = document.getElementById('previewImg');
    document.getElementById('previewModalLabel').textContent = label;

    frame.style.display = 'none';
    img.style.display   = 'none';

    if (ext === 'pdf') {
        frame.src = url;
        frame.style.display = 'block';
    } else {
        img.src = url;
        img.style.display = 'block';
    }
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// Reset preview on modal close
document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('previewFrame').src = '';
    document.getElementById('previewImg').src   = '';
});

// ── Delete modal ──────────────────────────────────────────────────────────────
function confirmDelete(actionUrl, label) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteDocLabel').textContent =
        'Are you sure you want to delete "' + label + '"?';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Auto-open upload modal on validation error ────────────────────────────────
@if($errors->any() && old('document_type'))
    window.addEventListener('DOMContentLoaded', function () {
        const typeKey  = '{{ old('document_type') }}';
        const labels   = @json(array_map(fn($t) => $t['label'], $docTypes));
        openUpload(typeKey, labels[typeKey] ?? typeKey, false);
    });
@endif
</script>
@endpush

@endsection
