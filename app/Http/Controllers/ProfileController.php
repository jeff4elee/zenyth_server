<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\ImageRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\UserPrivacyRepository;
use App\Repositories\UserRepository;
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
    private $userRepo;
    private $userPrivacyRepo;

    function __construct(ProfileRepository $profileRepo,
                        ImageRepository $imageRepo,
                        UserRepository $userRepo,
                        UserPrivacyRepository $userPrivacyRepo)
    {
        $this->profileRepo = $profileRepo;
        $this->imageRepo = $imageRepo;
        $this->userRepo = $userRepo;
        $this->userPrivacyRepo = $userPrivacyRepo;
    }

    /**
     * Read a user's profile
     * @param Request $request
     * @param $user_id
     * @return JsonResponse
     */
    public function read(Request $request, $user_id)
    {
        $currentUser = $request->get('user');

        // If the user being read is the same as the current user
        if($currentUser->id == $user_id) {
            return Response::dataResponse(true, [
                'user' => $currentUser
            ]);
        }

        $userBeingRead = $this->userRepo->read($user_id);
        // Create an array to be constructed based on privacy settings
        $userInfoArray = $userBeingRead->toArray();

        $userPrivacy = $userBeingRead->userPrivacy;

        // Take out the attribute if the privacy is self
        if($userPrivacy->email_privacy == 'self')
            array_pull($userInfoArray, 'email');

        if($userPrivacy->gender_privacy == 'self')
            array_pull($userInfoArray['profile'], 'gender');

        if($userPrivacy->birthday_privacy == 'self')
            array_pull($userInfoArray['profile'], 'birthday');

        // Only query for friends if any of the privacy settings has
        // friends scope. This way we save a query if none of the scopes
        // are friends
        if($userPrivacy->email_privacy == 'friends' ||
            $userPrivacy->gender_privacy == 'friends' ||
            $userPrivacy->birthday_privacy == 'friends') {

            // Get all friends ids of this user
            $friendsId = $userBeingRead->friendsId();
            $isFriend = in_array($currentUser->id, $friendsId);

            // If the user making the request is not friends with this user,
            // hide the attributes where privacy is friends only
            if(!$isFriend) {
                if ($userPrivacy->email_privacy == 'friends')
                    array_pull($userInfoArray, 'email');

                if ($userPrivacy->gender_privacy == 'friends')
                    array_pull($userInfoArray['profile'], 'gender');

                if ($userPrivacy->birthday_privacy == 'friends')
                    array_pull($userInfoArray['profile'], 'birthday');
            }
        }

        return Response::dataResponse(true, [
            'user' => $userInfoArray
        ]);
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

        if($request->has('email_privacy') ||
            $request->has('gender_privacy') ||
            $request->has('birthday_privacy')) {
            $this->userPrivacyRepo->update($request, $user->id, 'user_id');

            // This will show the userPrivacy in the response if it's being
            // updated
            $user->userPrivacy;
        }

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

        // Inject data into the request and send the data to imageRepo to create
        // an image
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
            'user' => $user
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
