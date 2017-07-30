<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\ImageRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ProfileController
 * @package App\Http\Controllers
 */
class ProfileController extends Controller
{
    private $profileRepo;
    private $imageRepo;

    function __construct(ProfileRepository $profileRepo,
                        ImageRepository $imageRepo)
    {
        $this->profileRepo = $profileRepo;
        $this->imageRepo = $imageRepo;
    }

    /**
     * Update profile
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->get('user');
        $this->profileRepo->update($request, $user->id, 'user_id');

        return Response::dataResponse(true, [
            'user' => $user
        ]);
    }

    /**
     * Update profile picture
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfilePicture(Request $request)
    {
        $user = $request->get('user');
        $profile = $user->profile;

        // Check for old profile picture, if there already is one, delete it
        if($oldImageId = $profile->picture_id) {
            $this->imageRepo->delete($oldImageId);
        }

        // UploadedFile object
        $image = $request->file('image');

        $request->merge([
            'user_id' => $user->id,
            'image_file' => $image,
            'directory' => 'profile_pictures',
            'imageable_id' => $profile->id,
            'imageable_type' => 'App\Profile'
        ]);
        $image = $this->imageRepo->create($request);

        $profile->picture_id = $image->id;
        $profile->update();


        return Response::dataResponse(true, [
            'profile' => $profile
        ]);
    }

    /**
     * Show profile image in raw format
     * @param $user_id
     * @return \Intervention\Image\Response
     */
    public function showProfileImage($user_id)
    {
        $profile = $this->profileRepo->findBy('user_id', $user_id);
        if($profile) {
            $image = $profile->profilePicture;
            if ($image == null)
                Exceptions::notFoundException('User does not have a profile picture');

            $filename = $image->filename;
            $path = 'app/profile_pictures/' . $filename;
            return Response::rawImageResponse($path);
        }

        Exceptions::notFoundException(INVALID_USER_ID);
    }

}
