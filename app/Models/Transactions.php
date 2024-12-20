<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Transactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'ip_address',
        'device_fingerprint',
        'is_new_device',
        'risk_score',
        'recommendation',
    ];

    //Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
