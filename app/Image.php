<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{

    protected $fillable = ['filename', 'imageable_id', 'imageable_type',
        'directory', 'user_id'];
    protected $hidden = ['directory', 'imageable_type', 'imageable_id'];
    protected $table = 'images';
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        Image::deleting(function($image) {
            Storage::disk($image->directory)->delete($image->filename);
        });
    }

    public function imageable()
    {
        return $this->morphTo();
    }

}
