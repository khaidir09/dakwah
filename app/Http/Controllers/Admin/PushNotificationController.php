<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationLog;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PushNotificationController extends Controller
{
    public function __construct(protected OneSignalService $oneSignal) {}

    public function index(Request $request)
    {
        $logs = PushNotificationLog::with('sender')
            ->latest()
            ->paginate(15);

        // Isi ulang pilihan pengguna setelah validasi/pengiriman gagal.
        $oldUsers = User::whereIn('id', (array) $request->old('user_ids', []))
            ->get(['id', 'name', 'email']);

        return view('pages.admin.push-notifications.index', compact('logs', 'oldUsers'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:300',
            'url' => 'nullable|url|max:255',
            'target' => ['required', Rule::in([PushNotificationLog::TARGET_ALL, PushNotificationLog::TARGET_USERS])],
            'user_ids' => 'required_if:target,'.PushNotificationLog::TARGET_USERS.'|array',
            'user_ids.*' => 'integer|distinct|exists:users,id',
        ]);

        $userIds = $validated['target'] === PushNotificationLog::TARGET_USERS
            ? array_map('intval', $validated['user_ids'])
            : null;

        $response = $userIds === null
            ? $this->oneSignal->sendToAll($validated['title'], $validated['message'], $validated['url'] ?? null)
            : $this->oneSignal->sendNotification($validated['title'], $validated['message'], $userIds, $validated['url'] ?? null);

        // Pengiriman yang gagal tetap dicatat agar admin tahu percobaan itu pernah dilakukan.
        $log = PushNotificationLog::create([
            'sent_by' => $request->user()->id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'url' => $validated['url'] ?? null,
            'target' => $validated['target'],
            'user_ids' => $userIds,
            'status' => $response === false ? PushNotificationLog::STATUS_FAILED : PushNotificationLog::STATUS_SENT,
            'onesignal_id' => $response['id'] ?? null,
            'recipients' => $response['recipients'] ?? null,
        ]);

        if ($log->status === PushNotificationLog::STATUS_FAILED) {
            return back()->withInput()
                ->with('error', 'Notifikasi gagal dikirim. Periksa kredensial OneSignal atau log aplikasi.');
        }

        $message = $log->recipients !== null
            ? "Notifikasi terkirim ke {$log->recipients} penerima."
            : 'Notifikasi terkirim.';

        return redirect()->route('admin.push-notifications.index')->with('message', $message);
    }
}
