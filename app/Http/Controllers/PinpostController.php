<?php

namespace App\Http\Controllers;

use App\Entity;
use App\EntitysPicture;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Image;
use App\Pinpost;
use App\Http\Traits\MathHelper;
use App\PinpostTag;
use App\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class PinpostController
 * @package App\Http\Controllers
 */
class PinpostController extends Controller
{
    use AuthenticationTrait;
    use MathHelper;

    protected $radiusQueryError = 'Fetching pinposts with type radius requires '
                        . 'the parameter radius';
    protected $frameQueryError = 'Fetching pinposts with type frame requires '
                        . 'the parameters first_coord and second_coord';

    /**
     * Create a Pinpost, storing thumbnail image if there is any
     * @param Request $request, post request
     *        rules: requires title, description, latitude,
     *          longitude, event_time
     * @return Pinpost information
     */
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
            // Tag must be in the form "tag1-tag2-tag3"
            // Must parse the hash tags out on client side
            $tags = $request->input('tags');
            $tags = explode("-", $tags);
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

        return Response::dataResponse(true, ['pinpost' => $pin],
            'Successfully created pinpost');

    }

    /**
     * Give back information on Pinpost
     * @param $pinpost_id
     * @return pin information, json response if pinpost not found
     */
    public function read($pinpost_id)
    {

        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        return Response::dataResponse(true, ['pinpost' => $pin]);

    }

    /**
     * Update Pinpost with information
     * @param Request $request, post request
     * @param $pinvite_id
     * @return pin information, json response if failed
     */
    public function update(Request $request, $pinpost_id)
    {

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        /* Checks if pinpost being updated belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Pinpost does not associate with this token');

        /* Updates title */
        if ($request->has('title'))
            $pin->title = $request->input('title');

        /* Updates description */
        if ($request->has('description'))
            $pin->description = $request->input('description');

        /* Updates thumbnail */
        if ($request->file('thumbnail') != null) {
            $image = Image::find($pin->thumbnail_id);
            $old_filename = $image->filename;
            ImageController::storeImage($request->file('thumbnail'), $image);

            if($old_filename != null)
                Storage::disk('images')->delete($old_filename);
        }

        /* Updates latitude */
        if ($request->has('latitude'))
            $pin->latitude = $request->input('latitude');

        /* Updates longitude */
        if ($request->has('longitude'))
            $pin->longitude = $request->input('longitude');

        $pin->update();

        return Response::dataResponse(true, ['pinpost' => $pin],
            'Successfully updated pinpost');

    }

    /**
     * Delete the pinpost
     * @param Request $request, delete request
     * @param $pinpost_id
     * @return json response
     */
    public function delete(Request $request, $pinpost_id)
    {

        /* Checks if pinpost is there */
        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        /* Checks if pinpost being deleted belongs to the user making the
            request */
        $api_token = $pin->creator->api_token;
        $headerToken = $request->header('Authorization');

        if ($api_token != $headerToken)
            Exceptions::invalidTokenException('Pinpost does not associate with this token');

        $pin->thumbnail->delete();
        $pin->entity->delete();

        return Response::successResponse('Successfully deleted pinpost');

    }

    /**
     * Fetch all pinposts of friends ordered by latest first
     * @param Request $request
     * @return mixed
     */
    public function fetch(Request $request)
    {
        $type = strtolower($request->input('type'));

        if($request->has('scope'))
            $scope = explode(",", strtolower($request->input('scope')));
        else
            $scope = array();

        if($type == 'radius')
            $pinposts = $this->getPinpostsInRadius($request);
        else
            $pinposts = $this->getPinpostsInFrame($request);

        if(in_array('self', $scope))
            $pinposts = $this->selfFilter($request, $pinposts);
        else if(in_array('friends', $scope))
            $pinposts = $this->friendsFilter($request, $pinposts);

        // Scope is either not provided or public. Return all pinposts in the
        // area
        return Response::dataResponse(true, [
            'pinposts' => $pinposts
        ]);
    }

    /**
     * Get all pinposts within the radius provided in the request
     * @param Request $request
     * @return array
     */
    public function getPinpostsInRadius(Request $request)
    {
        $radius = $request->input('radius');
        if($request->has('unit'))
            $unit = $request->input('unit');
        else
            $unit = 'mi';

        $center = explode(",", $request->input('center'));
        $centerLat = abs($center[0]);
        $centerLong = abs($center[1]);

        // Get all pinposts that are in the square surrounding the circle
        $pinposts = Pinpost::where([
            ['latitude', '>=', -($centerLat + $radius)],
            ['latitude', '<=', $centerLat + $radius],
            ['longitude', '>=', -($centerLong + $radius)],
            ['longitude', '<=', $centerLong + $radius]
        ])->latest()->get()->all(); // gets the array that contains pinposts

        // Filter pinposts to contain only the pinposts inside the circle
        $pinposts = array_filter($pinposts, function($pinpost)
        use($center, $unit, $radius) {
            $coord = [$pinpost['latitude'], $pinpost['longitude']];
            $distance = $this->distance($coord, $center, $unit);
            if($distance > $radius)
                return false;
            else
                return true;
        });

        return $pinposts;
    }

    /**
     * Get all pinposts in the box provided in the request
     * @param Request $request
     * @return mixed
     */
    public function getPinpostsInFrame(Request $request)
    {
        $firstCoord = explode(",", $request->input('first_coord'));
        $secondCoord = explode(",", $request->input('second_coord'));

        // The following logic is used to get the smaller and the larger of the
        // latitude and longitude so we can form one query. This is done so that
        // the user does not have to specifically specify which corner the
        // coordinate is
        if($firstCoord[0] > $secondCoord[0]) {
            $smallLat = $secondCoord[0];
            $largeLat = $firstCoord[0];
        } else {
            $smallLat = $firstCoord[0];
            $largeLat = $secondCoord[0];
        }

        if($firstCoord[1] > $secondCoord[1]) {
            $smallLong = $secondCoord[1];
            $largeLong = $firstCoord[1];
        } else {
            $smallLong = $firstCoord[1];
            $largeLong = $secondCoord[1];
        }

        // Get all pinposts inside the box
        $pinposts = Pinpost::where([
            ['latitude', '>=', $smallLat],
            ['latitude', '<=', $largeLat],
            ['longitude', '>=', $smallLong],
            ['longitude', '<=', $largeLong]
        ])->latest()->get()->all(); // gets the array that contains pinposts

        return $pinposts;
    }

    /**
     * Filter out all pinposts such that the result contains only the user's
     * and his friends' pinposts
     * @param Request $request
     * @param $pinposts
     * @return array
     */
    public function friendsFilter(Request $request, $pinposts)
    {
        $user = $request->get('user');
        $friendsId = $user->friendsId();

        $pinposts = array_filter($pinposts, function($pinpost)
                                use($user, $friendsId) {
            $creator_id = $pinpost['creator_id'];

            // If the creator is a friend or if the creator is the user
            // then include it in the result
            if(in_array($creator_id, $friendsId) || $creator_id == $user->id)
                return true;
            else
                return false;
        });

        return $pinposts;
    }

    /**
     * Filter out all pinposts such that the result contains only the user's
     * pinpost
     * @param Request $request
     * @param $pinposts
     * @return array
     */
    public function selfFilter(Request $request, $pinposts)
    {
        $user = $request->get('user');

        $pinposts = array_filter($pinposts, function($pinpost) use($user) {
            // Only include posts where the user is the creator
            if($pinpost['creator_id'] != $user->id)
                return false;
            else
                return true;
        });

        return $pinposts;
    }

}
