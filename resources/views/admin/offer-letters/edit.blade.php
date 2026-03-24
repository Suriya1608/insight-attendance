@extends('layouts.app')

@section('title', 'Edit Offer Letter')

@push('styles')
<style>
    .breadcrumb-bar {
        display: flex; align-items: center; gap: .375rem;
        font-size: .8125rem; color: var(--text-muted); margin-bottom: 1.25rem;
    }
    .breadcrumb-bar a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .breadcrumb-bar a:hover { text-decoration: underline; }
    .breadcrumb-bar .material-symbols-outlined { font-size: .9375rem; }

    .form-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        max-width: 760px;
    }
    .form-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: .625rem;
        background: #fafbfd;
    }
    .form-card-header .material-symbols-outlined { color: var(--primary); font-size: 1.25rem; }
    .form-card-header h5 { font-size: .9375rem; font-weight: 700; margin: 0; }
    .form-card-body { padding: 1.5rem; }

    .section-divider {
        font-size: .6875rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        padding-bottom: .4rem; margin: 1.5rem 0 1rem;
    }
    .section-divider:first-of-type { margin-top: 0; }

    .form-label {
        font-size: .8125rem; font-weight: 600; color: var(--text-main);
        margin-bottom: .375rem; display: block;
    }
    .form-control, .form-select {
        height: 2.625rem;
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--border);
        font-size: .875rem; color: var(--text-main);
        background: #f8fafc;
        transition: border-color .2s, box-shadow .2s, background .2s;
        width: 100%;
        padding: 0 .75rem;
    }
    textarea.form-control { height: auto; padding: .625rem .75rem; resize: vertical; }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-subtle);
        background: #fff; outline: none;
    }
    .form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; background-image: none; }
    .invalid-feedback { font-size: .8rem; color: #ef4444; margin-top: .25rem; display: block; }
    .form-hint { font-size: .8rem; color: var(--text-muted); margin-top: .3rem; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    @media (max-width: 640px) {
        .form-row, .form-row-3 { grid-template-columns: 1fr; }
    }

    .btn-save {
        height: 2.625rem; padding: 0 1.5rem;
        background: var(--primary); border: none;
        border-radius: var(--radius-sm); color: #fff;
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        cursor: pointer; transition: background .15s, box-shadow .15s;
        box-shadow: 0 2px 8px rgba(19,127,236,.3);
    }
    .btn-save:hover { background: var(--primary-hover); box-shadow: 0 4px 14px rgba(19,127,236,.4); }

    .btn-pdf {
        height: 2.625rem; padding: 0 1.25rem;
        background: #16a34a; border: none;
        border-radius: var(--radius-sm); color: #fff;
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none; cursor: pointer; transition: background .15s;
    }
    .btn-pdf:hover { background: #15803d; color: #fff; }

    .btn-cancel {
        height: 2.625rem; padding: 0 1.25rem;
        background: transparent; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); color: var(--text-secondary);
        font-size: .9rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .375rem;
        text-decoration: none; cursor: pointer; transition: all .15s;
    }
    .btn-cancel:hover { border-color: #94a3b8; color: var(--text-main); background: var(--bg-light); }
</style>
@endpush

@section('content')

    <div class="breadcrumb-bar">
        <a href="{{ route('admin.offer-letters.index') }}">Offer Letters</a>
        <span class="material-symbols-outlined">chevron_right</span>
        <span>Edit — {{ $offerLetter->name }}</span>
    </div>

    <div class="page-title">Edit Offer Letter</div>
    <p class="page-subtitle">Update the offer letter details for {{ $offerLetter->name }}.</p>

    <div class="form-card">
        <div class="form-card-header">
            <span class="material-symbols-outlined">edit_document</span>
            <h5>Offer Letter Details</h5>
        </div>
        <div class="form-card-body">

            <form method="POST" action="{{ route('admin.offer-letters.update', $offerLetter) }}" novalidate>
                @csrf
                @method('PUT')

                {{-- Candidate Info --}}
                <div class="section-divider">Candidate Information</div>

                <div class="mb-4">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $offerLetter->name) }}"
                           placeholder="e.g. John Doe"
                           maxlength="200" autofocus required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" rows="2"
                              class="form-control @error('address') is-invalid @enderror"
                              placeholder="Residential address"
                              maxlength="500">{{ old('address', $offerLetter->address) }}</textarea>
                    @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row mb-4">
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $offerLetter->email) }}"
                               placeholder="candidate@example.com"
                               maxlength="200">
                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="text" id="mobile" name="mobile"
                               class="form-control @error('mobile') is-invalid @enderror"
                               value="{{ old('mobile', $offerLetter->mobile) }}"
                               placeholder="+91 98765 43210"
                               maxlength="20">
                        @error('mobile') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Employment Details --}}
                <div class="section-divider">Employment Details</div>

                <div class="mb-4">
                    <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                    <input type="text" id="designation" name="designation"
                           class="form-control @error('designation') is-invalid @enderror"
                           value="{{ old('designation', $offerLetter->designation) }}"
                           placeholder="e.g. Software Engineer"
                           maxlength="200" required>
                    @error('designation') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row-3 mb-4">
                    <div>
                        <label for="ctc" class="form-label">CTC (Annual) <span class="text-danger">*</span></label>
                        <input type="number" id="ctc" name="ctc"
                               class="form-control @error('ctc') is-invalid @enderror"
                               value="{{ old('ctc', $offerLetter->ctc) }}"
                               placeholder="e.g. 600000"
                               min="0" step="1" required>
                        <div class="form-hint">Amount in ₹ per year.</div>
                        @error('ctc') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="offer_date" class="form-label">Offer Date <span class="text-danger">*</span></label>
                        <input type="date" id="offer_date" name="offer_date"
                               class="form-control @error('offer_date') is-invalid @enderror"
                               value="{{ old('offer_date', $offerLetter->offer_date->format('Y-m-d')) }}" required>
                        @error('offer_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="joining_date" class="form-label">Joining Date <span class="text-danger">*</span></label>
                        <input type="date" id="joining_date" name="joining_date"
                               class="form-control @error('joining_date') is-invalid @enderror"
                               value="{{ old('joining_date', $offerLetter->joining_date->format('Y-m-d')) }}" required>
                        @error('joining_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Additional Content --}}
                <div class="section-divider">Additional Notes (optional)</div>

                <div class="mb-4">
                    <label for="content" class="form-label">Special Terms / Notes</label>
                    <textarea id="content" name="content" rows="4"
                              class="form-control @error('content') is-invalid @enderror"
                              placeholder="Any special terms, conditions, or notes to include in the offer letter…">{{ old('content', $offerLetter->content) }}</textarea>
                    <div class="form-hint">This will appear in the offer letter under Additional Terms.</div>
                    @error('content') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="d-flex align-items-center gap-2"
                     style="border-top:1px solid var(--border); margin-top:1.25rem; padding-top:1.25rem;">
                    <button type="submit" class="btn-save">
                        <span class="material-symbols-outlined" style="font-size:1rem">save</span>
                        Update Offer Letter
                    </button>
                    <a href="{{ route('admin.offer-letters.pdf', $offerLetter) }}" class="btn-pdf" target="_blank">
                        <span class="material-symbols-outlined" style="font-size:1rem">picture_as_pdf</span>
                        Download PDF
                    </a>
                    <a href="{{ route('admin.offer-letters.index') }}" class="btn-cancel">
                        <span class="material-symbols-outlined" style="font-size:1rem">close</span>
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

@endsection
