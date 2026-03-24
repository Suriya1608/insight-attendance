<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OfferLetterMail;
use App\Models\OfferLetter;
use App\Models\OfferLetterEmail;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OfferLetterController extends Controller
{
    public function index()
    {
        $offerLetters = OfferLetter::withCount('emailLogs')
            ->with(['emailLogs' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->paginate(20);

        return view('admin.offer-letters.index', compact('offerLetters'));
    }

    public function create()
    {
        return view('admin.offer-letters.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:200'],
            'address'      => ['nullable', 'string', 'max:500'],
            'email'        => ['nullable', 'email', 'max:200'],
            'mobile'       => ['nullable', 'string', 'max:20'],
            'designation'  => ['required', 'string', 'max:200'],
            'joining_date' => ['required', 'date'],
            'ctc'          => ['required', 'numeric', 'min:0'],
            'offer_date'   => ['required', 'date'],
            'content'      => ['nullable', 'string'],
        ]);

        OfferLetter::create($data);

        return redirect()->route('admin.offer-letters.index')
            ->with('success', 'Offer letter for ' . $data['name'] . ' created successfully.');
    }

    public function edit(OfferLetter $offerLetter)
    {
        return view('admin.offer-letters.edit', compact('offerLetter'));
    }

    public function update(Request $request, OfferLetter $offerLetter)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:200'],
            'address'      => ['nullable', 'string', 'max:500'],
            'email'        => ['nullable', 'email', 'max:200'],
            'mobile'       => ['nullable', 'string', 'max:20'],
            'designation'  => ['required', 'string', 'max:200'],
            'joining_date' => ['required', 'date'],
            'ctc'          => ['required', 'numeric', 'min:0'],
            'offer_date'   => ['required', 'date'],
            'content'      => ['nullable', 'string'],
        ]);

        $offerLetter->update($data);

        return redirect()->route('admin.offer-letters.index')
            ->with('success', 'Offer letter for ' . $data['name'] . ' updated successfully.');
    }

    public function destroy(OfferLetter $offerLetter)
    {
        $name = $offerLetter->name;
        $offerLetter->delete();

        return back()->with('success', 'Offer letter for "' . $name . '" deleted.');
    }

    public function downloadPdf(OfferLetter $offerLetter)
    {
        $settings = SiteSetting::allKeyed();
        $logoPath = $this->resolveLogoPath($settings);

        $pdf = Pdf::loadView('admin.offer-letters.pdf', compact('offerLetter', 'settings', 'logoPath'))
            ->setPaper('A4', 'portrait')
            ->setOption([
                'dpi'                      => 150,
                'isRemoteEnabled'          => false,
                'defaultMediaType'         => 'print',
                'isFontSubsettingEnabled'  => true,
            ]);

        $filename = 'Offer-Letter-' . str_replace(' ', '-', $offerLetter->name) . '-' . $offerLetter->id . '.pdf';

        return $pdf->download($filename);
    }

    /* ─────────────────────────────────────────
     |  Email Actions
     ───────────────────────────────────────── */

    public function sendEmail(OfferLetter $offerLetter)
    {
        return $this->dispatchEmail($offerLetter, isResend: false);
    }

    public function resendEmail(OfferLetter $offerLetter)
    {
        return $this->dispatchEmail($offerLetter, isResend: true);
    }

    public function emailHistory(OfferLetter $offerLetter)
    {
        $logs = $offerLetter->emailLogs()->latest()->get();

        return view('admin.offer-letters.email-history', compact('offerLetter', 'logs'));
    }

    /* ─────────────────────────────────────────
     |  Private Helpers
     ───────────────────────────────────────── */

    private function dispatchEmail(OfferLetter $offerLetter, bool $isResend): \Illuminate\Http\RedirectResponse
    {
        if (!$offerLetter->email) {
            return back()->with('error', 'No email address on record for ' . $offerLetter->name . '. Please edit the offer letter and add an email first.');
        }

        $settings    = SiteSetting::allKeyed();
        $logoPath    = $this->resolveLogoPath($settings);
        $companyName = $settings['site_name'] ?? config('app.name');

        $pdf = Pdf::loadView('admin.offer-letters.pdf', compact('offerLetter', 'settings', 'logoPath'))
            ->setPaper('A4', 'portrait')
            ->setOption([
                'dpi'                      => 150,
                'isRemoteEnabled'          => false,
                'defaultMediaType'         => 'print',
                'isFontSubsettingEnabled'  => true,
            ]);

        $filename   = 'Offer-Letter-' . str_replace(' ', '-', $offerLetter->name) . '-' . $offerLetter->id . '.pdf';
        $pdfContent = $pdf->output();

        try {
            Mail::to($offerLetter->email)
                ->send(new OfferLetterMail($offerLetter, $pdfContent, $filename, $companyName));

            $offerLetter->emailLogs()->create([
                'email'   => $offerLetter->email,
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            $label = $isResend ? 'resent' : 'sent';

            return back()->with('success', "Offer letter {$label} successfully to {$offerLetter->email}.");
        } catch (\Throwable $e) {
            $offerLetter->emailLogs()->create([
                'email'         => $offerLetter->email,
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at'       => now(),
            ]);

            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    private function resolveLogoPath(array $settings): ?string
    {
        if (empty($settings['site_logo'])) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $settings['site_logo']);

        if (!file_exists($fullPath)) {
            return null;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fullPath));
    }
}
