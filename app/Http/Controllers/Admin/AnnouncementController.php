<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::with('author')->latest()->get(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $isPublished = $request->boolean('is_published');

        Announcement::create([
            ...$request->safe()->except('cover'),
            'cover_path' => $request->file('cover')?->store('announcements', 'public'),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
            'author_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $attributes = $request->safe()->except('cover');
        $attributes['is_published'] = $request->boolean('is_published');

        if ($attributes['is_published'] && $announcement->published_at === null) {
            $attributes['published_at'] = now();
        }

        if ($request->hasFile('cover')) {
            if ($announcement->cover_path !== null) {
                Storage::disk('public')->delete($announcement->cover_path);
            }

            $attributes['cover_path'] = $request->file('cover')->store('announcements', 'public');
        }

        $announcement->update($attributes);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }
}
