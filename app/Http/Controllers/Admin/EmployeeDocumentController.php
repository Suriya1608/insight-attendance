<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeDocumentController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(User $employee)
    {
        $docTypes  = config('employee_documents.types', []);
        $documents = EmployeeDocument::where('employee_id', $employee->id)
            ->with('uploader')
            ->get()
            ->keyBy('document_type');

        $mandatory     = array_keys(array_filter($docTypes, fn($t) => $t['mandatory']));
        $uploadedTypes = $documents->keys()->toArray();
        $missingMandatory = array_diff($mandatory, $uploadedTypes);

        return view('admin.employees.documents.index', compact(
            'employee', 'docTypes', 'documents', 'missingMandatory'
        ));
    }

    // ── Store (upload / replace) ───────────────────────────────────────────────

    public function store(Request $request, User $employee)
    {
        $docTypes    = config('employee_documents.types', []);
        $maxKb       = config('employee_documents.max_size_kb', 2048);
        $allowedExts = config('employee_documents.allowed_types', ['pdf', 'jpg', 'jpeg', 'png']);

        $request->validate([
            'document_type' => ['required', 'string', 'in:' . implode(',', array_keys($docTypes))],
            'document_file' => [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExts),
                'max:' . $maxKb,
            ],
        ]);

        $type = $request->input('document_type');
        $file = $request->file('document_file');

        // Unique filename: {type}_{uuid}.{ext}
        $ext        = strtolower($file->getClientOriginalExtension());
        $uniqueName = $type . '_' . Str::uuid() . '.' . $ext;
        $storagePath = "employee-documents/{$employee->id}/{$uniqueName}";

        // Check for existing record
        $existing = EmployeeDocument::where('employee_id', $employee->id)
            ->where('document_type', $type)
            ->first();

        $oldValue = null;

        if ($existing) {
            $oldValue = [
                'file_name' => $existing->file_name,
                'file_path' => $existing->file_path,
            ];

            // Archive or delete old file
            if (Storage::disk('local')->exists($existing->file_path)) {
                if (config('employee_documents.archive_on_replace', true)) {
                    $archiveName = pathinfo($existing->file_path, PATHINFO_FILENAME)
                        . '_' . now()->format('Ymd_His')
                        . '.' . pathinfo($existing->file_path, PATHINFO_EXTENSION);
                    Storage::disk('local')->move(
                        $existing->file_path,
                        "employee-documents/archive/{$employee->id}/{$archiveName}"
                    );
                } else {
                    Storage::disk('local')->delete($existing->file_path);
                }
            }
        }

        // Store new file on private disk
        Storage::disk('local')->put($storagePath, file_get_contents($file->getRealPath()));

        $newValue = [
            'type'      => $type,
            'label'     => $docTypes[$type]['label'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storagePath,
        ];

        if ($existing) {
            $existing->update([
                'file_path'   => $storagePath,
                'file_name'   => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
            ]);
            $document = $existing->fresh();
            $action   = 'replace';
        } else {
            $document = EmployeeDocument::create([
                'employee_id'   => $employee->id,
                'document_type' => $type,
                'file_path'     => $storagePath,
                'file_name'     => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
            $action = 'upload';
        }

        AuditLog::record(
            module:    'Employee Documents',
            action:    $action,
            recordId:  $document->id,
            userId:    $employee->id,
            oldValue:  $oldValue,
            newValue:  $newValue,
        );

        $label = $docTypes[$type]['label'];
        return redirect()
            ->route('admin.employees.documents.index', $employee)
            ->with('success', "\"{$label}\" " . ($action === 'replace' ? 'replaced' : 'uploaded') . ' successfully.');
    }

    // ── View (inline) ─────────────────────────────────────────────────────────

    public function view(User $employee, EmployeeDocument $document)
    {
        $this->authorizeDocument($employee, $document);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $mime = $this->resolveMime($document->extension());

        return response(Storage::disk('local')->get($document->file_path), 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($document->file_name) . '"',
            'X-Frame-Options'     => 'SAMEORIGIN',
            'Cache-Control'       => 'private, no-store',
        ]);
    }

    // ── Download ──────────────────────────────────────────────────────────────

    public function download(User $employee, EmployeeDocument $document)
    {
        $this->authorizeDocument($employee, $document);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        AuditLog::record(
            module:   'Employee Documents',
            action:   'download',
            recordId: $document->id,
            userId:   $employee->id,
        );

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name
        );
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(User $employee, EmployeeDocument $document)
    {
        $this->authorizeDocument($employee, $document);

        $oldValue = [
            'type'      => $document->document_type,
            'label'     => $document->typeLabel(),
            'file_name' => $document->file_name,
            'file_path' => $document->file_path,
        ];

        // Archive or delete file
        if (Storage::disk('local')->exists($document->file_path)) {
            if (config('employee_documents.archive_on_replace', true)) {
                $archiveName = pathinfo($document->file_path, PATHINFO_FILENAME)
                    . '_deleted_' . now()->format('Ymd_His')
                    . '.' . $document->extension();
                Storage::disk('local')->move(
                    $document->file_path,
                    "employee-documents/archive/{$employee->id}/{$archiveName}"
                );
            } else {
                Storage::disk('local')->delete($document->file_path);
            }
        }

        AuditLog::record(
            module:   'Employee Documents',
            action:   'delete',
            recordId: $document->id,
            userId:   $employee->id,
            oldValue: $oldValue,
        );

        $label = $document->typeLabel();
        $document->delete();

        return redirect()
            ->route('admin.employees.documents.index', $employee)
            ->with('success', "\"{$label}\" deleted successfully.");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** Ensure the document belongs to the given employee. */
    private function authorizeDocument(User $employee, EmployeeDocument $document): void
    {
        abort_unless((int) $document->employee_id === (int) $employee->id, 404);
    }

    private function resolveMime(string $ext): string
    {
        return match ($ext) {
            'pdf'        => 'application/pdf',
            'jpg', 'jpeg'=> 'image/jpeg',
            'png'        => 'image/png',
            default      => 'application/octet-stream',
        };
    }
}
