<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['pinpost_id'];
    protected $table = 'tags';

    public function pinpost() {
        return $this->belongsToMany('App\Pinpost', 'pinpost');
    }
}
