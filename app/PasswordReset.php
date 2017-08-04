<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $fillable = ['email', 'token'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User', 'email');
    }
}
