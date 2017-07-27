<?php

namespace App\Http\Controllers;

use App\Address;
use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\PhoneNumber;
use App\Image;
use App\Repositories\ImageRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $profile = $this->profileRepo->update($request, $user->id, 'user_id');

        if($request->hasFile('image')) {
            $request->merge([
                'image_file' => $request->file('image'),
                'directory' => 'profile_pictures'
            ]);
            if($profile->image_id) {
                $this->imageRepo->update($request, $profile->image_id);
            }
            else {
                $image = $this->imageRepo->create($request);
                $profile->image_id = $image->id;
                $profile->update();
            }
        }

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
            if ($image == null) {
                Exceptions::notFoundException('User does not have a profile picture');
            }
            $filename = $image->filename;
            $path = 'app/profile_pictures/' . $filename;
            return Response::rawImageResponse($path);
        }

        Exceptions::notFoundException('Invalid user id');
    }

}
