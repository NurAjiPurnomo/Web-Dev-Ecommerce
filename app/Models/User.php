<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'birth_date',
        'avatar',
        'address',
        'city',
        'province',
        'postal_code',
        'google_id',
        'password',
        'is_admin',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')
                    ->withPivot('is_used', 'used_at')
                    ->withTimestamps();
    }
}
