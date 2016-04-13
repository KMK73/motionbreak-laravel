<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function movements(){
        $this->hasMany('App\CompletedMovement');
    }
    
    public function locations() {
        $this->hasMany('App\UserLocation');
    }
    
    public function break_interval() {
        $this->hasOne('App\UserBreak', 'user_id');
    }
}
