<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasEncryptedRouteKey;
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'reference_id',
        'url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a notification for a user.
     */
    public static function notify(
        int    $userId,
        string $title,
        string $message,
        string $type        = 'general',
        ?int   $referenceId = null,
        ?string $url        = null
    ): void {
        static::create([
            'user_id'      => $userId,
            'title'        => $title,
            'message'      => $message,
            'type'         => $type,
            'reference_id' => $referenceId,
            'url'          => $url,
            'is_read'      => false,
        ]);
    }

    /**
     * Unread count for a user.
     */
    public static function unreadCount(int $userId): int
    {
        return static::where('user_id', $userId)->where('is_read', false)->count();
    }
}
