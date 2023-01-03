<?php

namespace App\Models;

use App\Models\Merchant\merchantCompany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends=['image_url'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    public function getImageUrlAttribute($value)
    {
        return asset('images/profile/' . ($this->avatar ?: 'user-default.png'));
    }
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function avatar()
    {
        return asset('images/logo.png');
    }

    public function balance()
    {
        return floatval($this->balance);
    }
   public function notificationSetting()
   {
       return $this->hasMany(notificationSetting::class);
   }
    public function lender()
    {
        return $this->belongsTo(self::class, 'lender_id');
    }
    public function company()
    {
        return $this->hasOne(merchantCompany::class);
    }

    public function lenderEmail()
    {
        return $this->lender ? $this->lender->email : 'N/A';
    }

    public function myUsers()
    {
        return $this->hasMany(self::class, 'lender_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function paidBills()
    {
        return $this->hasMany(PaidBill::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function myApplications()
    {
        return $this->hasMany(UserApplication::class, 'user_id');
    }

    public function scopeNotDeleted($q, $value = 0)
    {
        return $q->where('is_deleted', $value);
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *bbbbb
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function routeNotificationForNexmo($notification)
    {
        return $this->phone;
    }
}
