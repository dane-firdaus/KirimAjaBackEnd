<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'mst_user';

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
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fullname', 'email', 'password', 'phone', 'verified', 'department', 'nopeg'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','created_at','updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    public function branchOffice()
    {
        return $this->hasOne('App\BranchOffice', 'code', 'city_code');
    }

    public function identityCard()
    {
        return $this->hasOne('App\UserIdentityCard', 'user_id', 'id');
    }

    public function booking()
    {
        return $this->hasMany('App\Booking', 'user_id', 'id');
    }

    public function wallet()
    {
        return $this->hasOne('App\WalletAccount', 'user_id', 'id');
    }

    public function getKotaAttribute($value)
    {
        return trim($value);
    }

    public function getProvinsiAttribute($value)
    {
        return trim($value);
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
