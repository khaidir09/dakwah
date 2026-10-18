<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationLog extends Model
{
    public const TARGET_ALL = 'all';

    public const TARGET_USERS = 'users';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'sent_by',
        'title',
        'message',
        'url',
        'target',
        'user_ids',
        'status',
        'onesignal_id',
        'recipients',
    ];

    protected $casts = [
        'user_ids' => 'array',
        'recipients' => 'integer',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
