<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('announcements.index', [
            'announcements' => Announcement::published()->latest('published_at')->paginate(9),
        ]);
    }

    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->is_published && $announcement->published_at?->isPast(), 404);

        return view('announcements.show', [
            'announcement' => $announcement,
        ]);
    }
}
