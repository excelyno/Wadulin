<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttachmentType;
use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Models\Complaint;
use App\Notifications\ComplaintUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ComplaintStatusController extends Controller
{
    public function __invoke(UpdateComplaintStatusRequest $request, Complaint $complaint): RedirectResponse
    {
        $target = ComplaintStatus::from($request->validated('status'));
        $user = $request->user();

        DB::transaction(function () use ($request, $complaint, $target, $user): void {
            $complaint->transitionTo(
                $target,
                $user,
                $request->validated('note'),
                $request->boolean('is_public', true),
            );

            foreach ($request->file('photos', []) as $photo) {
                $complaint->attachments()->create([
                    'type' => AttachmentType::Hasil,
                    'file_path' => $photo->store('complaints/'.$complaint->ticket_code, 'public'),
                    'mime_type' => $photo->getMimeType(),
                    'size_kb' => (int) ceil($photo->getSize() / 1024),
                    'uploaded_by' => $user->id,
                ]);
            }
        });

        $complaint->user->notify(new ComplaintUpdated(
            $complaint,
            "Status pengaduan kamu kini {$target->label()}.".($request->boolean('is_public', true) && filled($request->validated('note')) ? ' Catatan: '.$request->validated('note') : ''),
        ));

        return back()->with('success', "Status pengaduan {$complaint->ticket_code} diubah menjadi {$target->label()}.");
    }
}
