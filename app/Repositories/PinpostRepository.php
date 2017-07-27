<?php

namespace App\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Image;
use App\Pinpost;
use App\PinpostTag;
use App\Tag;
use App\EntitysPicture;
use App\Entity;
use App\Http\Controllers\ImageController;

class PinpostRepository extends Repository
{
    /**
     * Specify Model class name
     * @return mixed
     */
    function model()
    {
        return 'App\Pinpost';
    }

    public function create(Request $request)
    {
        $pin = new Pinpost();
        $entity = Entity::create([]);

        $pin->title = $request->input('title');
        $pin->description = $request->input('description');
        $pin->latitude = $request->input('latitude');
        $pin->longitude = $request->input('longitude');

        /* Checks if a thumbnail was provided */
        if ($request->file('thumbnail') != null) {
            $image = new Image();
            $entitys_picture = new EntitysPicture();
            ImageController::storeImage($request->file('thumbnail'), $image);
            $image->save();
            $pin->thumbnail_id = $image->id;
            $entitys_picture->entity_id = $entity->id;
            $entitys_picture->image_id = $image->id;
            $entitys_picture->save();
        }

        $pin->entity_id = $entity->id;

        $user = $request->get('user');
        $pin->creator_id = $user->id;

        $pin->save();

        if($request->has('tags')) {
            // Tag must be in the form "tag1,tag2,tag3"
            // Must parse the hash tags out on client side
            $tags = strtolower($request->input('tags'));
            $tags = explode(",", $tags);
            foreach($tags as $tag_name) {
                $tag = Tag::where('tag', $tag_name)->first();

                // If tag already exists, create another PinpostTag that
                // associates with this pinpost and the tag
                if($tag) {
                    PinpostTag::create([
                        'pinpost_id' => $pin->id,
                        'tag_id' => $tag->id
                    ]);
                }
                // If tag does not exist, create one
                else {
                    $tag = Tag::create(['tag' => $tag_name]);
                    PinpostTag::create([
                        'pinpost_id' => $pin->id,
                        'tag_id' => $tag->id
                    ]);
                }
            }
        }

        return $pin;

    }

}