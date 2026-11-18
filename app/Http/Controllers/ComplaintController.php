<?php

namespace App\Http\Controllers;

use App\Enums\AttachmentType;
use App\Enums\ComplaintStatus;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use App\Models\QrLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $status = ComplaintStatus::tryFrom($request->string('status')->toString());

        return view('complaints.index', [
            'complaints' => $request->user()->complaints()
                ->with(['category', 'district'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'activeStatus' => $status,
        ]);
    }

    /**
     * A `qr` query string (from a scanned QR poster) pre-fills the location.
     */
    public function create(Request $request): View
    {
        $qrCode = $request->string('qr')->trim()->toString();

        return view('complaints.create', [
            'categories' => Category::active()->orderBy('name')->get(),
            'districts' => District::orderBy('name')->get(),
            'qrLocation' => $qrCode === '' ? null : QrLocation::active()->with('district')->where('code', $qrCode)->first(),
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $user = $request->user();

        $complaint = DB::transaction(function () use ($request, $user): Complaint {
            $complaint = Complaint::create([
                ...$request->safe()->except('photos'),
                'ticket_code' => Complaint::nextTicketCode(),
                'user_id' => $user->id,
                'status' => ComplaintStatus::Pending,
                'due_at' => Complaint::calculateDueAt(now()),
            ]);

            $complaint->statusHistories()->create([
                'changed_by' => $user->id,
                'from_status' => null,
                'to_status' => ComplaintStatus::Pending,
            ]);

            foreach ($request->file('photos', []) as $photo) {
                $complaint->attachments()->create([
                    'type' => AttachmentType::Bukti,
                    'file_path' => $photo->store('complaints/'.$complaint->ticket_code, 'public'),
                    'mime_type' => $photo->getMimeType(),
                    'size_kb' => (int) ceil($photo->getSize() / 1024),
                    'uploaded_by' => $user->id,
                ]);
            }

            return $complaint;
        });

        return redirect()
            ->route('pengaduan.show', $complaint)
            ->with('success', 'Pengaduan terkirim! Simpan kode tiket ini untuk melacak perkembangannya.');
    }

    public function show(Complaint $complaint): View
    {
        Gate::authorize('view', $complaint);

        $complaint->load([
            'category',
            'district',
            'attachments',
            'statusHistories' => fn ($query) => $query->where('is_public', true),
            'dispositions.toDistrict',
        ]);

        return view('complaints.show', [
            'complaint' => $complaint,
        ]);
    }
}
