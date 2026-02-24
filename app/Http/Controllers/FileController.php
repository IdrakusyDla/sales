<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyLog;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    /**
     * Serve daily log photos (start_photo, start_odo_photo, end_photo, end_odo_photo)
     */
    public function dailyPhoto(Request $request, $id, $kind)
    {
        $user = Auth::user();

        $allowed = ['start_photo', 'start_odo_photo', 'end_photo', 'end_odo_photo'];
        if (! in_array($kind, $allowed)) {
            abort(404);
        }

        $dailyLog = DailyLog::with('user')->findOrFail($id);

        $filePath = $dailyLog->{$kind} ?? null;
        if (! $filePath) {
            abort(404);
        }

        // Authorization
        if ($user->isSales()) {
            if ($dailyLog->user_id !== $user->id) abort(403);
        } elseif ($user->isSupervisor()) {
            $owner = $dailyLog->user;
            $isOwn = $owner->id === $user->id;
            $isSub = $owner->supervisor_id === $user->id || $owner->supervisors()->where('supervisor_sales.supervisor_id', $user->id)->exists();
            if (! $isOwn && ! $isSub) abort(403);
        } elseif (! in_array($user->role, ['hrd', 'finance', 'it'])) {
            abort(403);
        }

        $path = storage_path('app/public/' . $filePath);
        if (! file_exists($path)) abort(404);

        return response()->file($path);
    }

    /**
     * Serve visit photo
     */
    public function visitPhoto(Request $request, $id)
    {
        $user = Auth::user();

        $visit = Visit::with('dailyLog.user')->findOrFail($id);
        $filePath = $visit->photo_path ?? null;
        if (! $filePath) abort(404);

        // Authorization (owner of daily log or supervisor or HRD/Finance/IT)
        $owner = $visit->dailyLog->user;
        if ($user->isSales()) {
            if ($owner->id !== $user->id) abort(403);
        } elseif ($user->isSupervisor()) {
            $isOwn = $owner->id === $user->id;
            $isSub = $owner->supervisor_id === $user->id || $owner->supervisors()->where('supervisor_sales.supervisor_id', $user->id)->exists();
            if (! $isOwn && ! $isSub) abort(403);
        } elseif (! in_array($user->role, ['hrd', 'finance', 'it'])) {
            abort(403);
        }

        $path = storage_path('app/public/' . $filePath);
        if (! file_exists($path)) abort(404);

        return response()->file($path);
    }
}
