<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Message extends Model
{
    use Searchable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'title',
        'content',
    ];

    /**
     * Get the user that owns the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Mesajı alan kullanıcı
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
