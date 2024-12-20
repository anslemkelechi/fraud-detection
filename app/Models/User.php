<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'shipping_address',
        'device_fingerprint',
        'location_ip',
        'security_question',
        'location',
    ];



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'security_question' => 'json',
        'location' => 'json',
    ];

    //Relationships
    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    public function transactionsIps()
    {
        return $this->hasMany(TransactionIps::class);
    }

    public function blacklistedIps()
    {
        return $this->hasMany(BlacklistedIps::class);
    }
}
