<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    /**
     * Show preferences form
     */
    public function edit()
    {
        $user = Auth::user();
        $preferences = NotificationPreference::getForUser($user->id);

        return view('notification_preferences.edit', compact('user', 'preferences'));
    }

    /**
     * Update preferences
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $preferences = NotificationPreference::getForUser($user->id);

        $preferences->update([
            'email_on_approval_needed' => $request->boolean('email_on_approval_needed'),
            'email_on_approved' => $request->boolean('email_on_approved'),
            'email_on_rejected' => $request->boolean('email_on_rejected'),
            'email_digest_weekly' => $request->boolean('email_digest_weekly'),
        ]);

        return redirect()->back()->with('success', 'Preferensi berhasil disimpan');
    }
}
