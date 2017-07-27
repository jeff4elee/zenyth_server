<?php

namespace App\Http\Controllers;

use App\Entity;
use App\EntitysPicture;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\Image;
use App\Pinpost;
use App\PinpostTag;
use App\Repositories\Criteria\Pinpost\FrameCriteria;
use App\Repositories\Criteria\Pinpost\FriendsScope;
use App\Repositories\Criteria\Pinpost\LatestPinpost;
use App\Repositories\Criteria\Pinpost\RadiusCriteria;
use App\Repositories\Criteria\Pinpost\SelfScope;
use App\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PinpostRepository as PinpostRepo;

/**
 * Class PinpostController
 * @package App\Http\Controllers
 */
class PinpostController extends Controller
{
    use AuthenticationTrait;

    private $pinpostRepo;

    public function __construct(PinpostRepo $pinpostRepo)
    {
        $this->pinpostRepo = $pinpostRepo;
    }

    /**
     * Create a Pinpost, storing thumbnail image if there is any
     * @param Request $request, post request
     *        rules: requires title, description, latitude,
     *          longitude, event_time
     * @return response
     */
    public function create(Request $request)
    {
        $pin = $this->pinpostRepo->create($request);

        Cache::put('pinpost-'.$pin->id, $pin);

        return Response::dataResponse(true, ['pinpost' => $pin],
            'Successfully created pinpost');

    }

    /**
     * Give back information on Pinpost
     * @param $pinpost_id
     * @return response
     */
    public function read($pinpost_id)
    {

        if(Cache::has('pinpost-'.$pinpost_id)){
            return Response::dataResponse(true, ['pinpost' => Cache::get('pinpost-'.$pinpost_id)]);
        }

        $pin = Pinpost::find($pinpost_id);

        if ($pin == null)
            Exceptions::notFoundException('Pinpost not found');

        return Response::dataResponse(true, ['pinpost' => $pin]);

    }

    /**
     * Update Pinpost with information
     * @param Request $request, post request
     * @param $pinvite_id
     * @return response
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

        Cache::put('pinpost-'.$pin->id, $pin);
        return Response::dataResponse(true, ['pinpost' => $pin],
            'Successfully updated pinpost');

    }

    /**
     * Delete the pinpost
     * @param Request $request, delete request
     * @param $pinpost_id
     * @return response
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

        Cache::forget('pinpost-'.$pin->id);
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
        $user = $request->get('user');

        if($request->has('scope'))
            $scope = explode(",", strtolower($request->input('scope')));
        else
            $scope = array();

        if($type == 'radius')
            $this->pinpostRepo->pushCriteria(
                new RadiusCriteria($request->all()));
        else
            $this->pinpostRepo->pushCriteria(new FrameCriteria($request->all()));

        if(in_array('self', $scope))
            $this->pinpostRepo->pushCriteria(new SelfScope($user));
        else if(in_array('friends', $scope))
            $this->pinpostRepo->pushCriteria(new FriendsScope($user));

        $this->pinpostRepo->pushCriteria(new LatestPinpost());

        // FriendsScope is either not provided or public. Return all pinposts in the
        // area
        return Response::dataResponse(true, [
            'pinposts' => $this->pinpostRepo->all() // get all the pinposts
        ]);
    }

}
