<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_type',
        'file_path',
        'file_name',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /** Human-readable label for this document type. */
    public function typeLabel(): string
    {
        return config("employee_documents.types.{$this->document_type}.label", ucwords(str_replace('_', ' ', $this->document_type)));
    }

    /** Extension of the stored file (lowercase). */
    public function extension(): string
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    /** Whether this document can be shown inline in the browser (PDF / image). */
    public function isInlineViewable(): bool
    {
        return in_array($this->extension(), ['pdf', 'jpg', 'jpeg', 'png']);
    }
}
