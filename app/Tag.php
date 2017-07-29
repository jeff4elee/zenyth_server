<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];
    protected $table = 'tags';
    protected $hidden = ['pivot', 'id'];
    public $timestamps = false;

    public function pinposts() {
        return $this->morphedByMany('App\Pinpost', 'taggable');
    }

}
