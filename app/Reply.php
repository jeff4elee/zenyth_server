<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $table = 'replies';
    protected $fillable = ['text', 'user_id', 'comment_id'];
    protected $visible = ['id', 'text', 'user_id', 'comment_id', 'creator',
        'updated_at', 'created_at'];

    protected static function boot()
    {
        parent::boot();
        Reply::deleting(function($reply) {
            foreach($reply->images as $image)
                $image->delete();

            foreach($reply->likes as $like)
                $like->delete();
        });
    }

    public function comment()
    {
        return $this->belongsTo('App\Comment', 'comment_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function images()
    {
        return $this->morphMany('App\Image', 'imageable');
    }

    public function likes()
    {
        return $this->morphMany('App\Like', 'likeable');
    }

    public function likesCount()
    {
        return $this->likes()->count();
    }

    public function toArray()
    {
        $response = parent::toArray();
        if(!in_array('likes_count', $this->hidden))
            $response['likes_count'] = $this->likesCount();

        if(in_array('likes', $this->visible))
            $response['likes'] = $this->likes;

        $response['images'] = $this->images;
        if(!in_array('creator', $this->hidden))
            $response['creator'] = $this->creator;
        return $response;
    }
}
