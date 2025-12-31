<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'date_of_birth',
        'account_type',
        'email',
        'avatar_url',
        'identity_document_url',
        'password',
        'wallet_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function appartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'renter_id');
    }

    public function ownerConservations()
    {
        return $this->hasMany(Conservation::class, 'owner_id');
    }

    public function renterConservations()
    {
        return $this->hasMany(Conservation::class, 'renter_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
