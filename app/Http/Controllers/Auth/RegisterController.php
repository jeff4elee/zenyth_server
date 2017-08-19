<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Controller;
use App\Repositories\ImageRepository;
use App\Repositories\OauthRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\UserPrivacyRepository;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;
    use AuthenticationTrait;

    private $userRepo;
    private $profileRepo;
    private $oauthRepo;
    private $imageRepo;
    private $userPrivacyRepo;

    /**
     * Create a new controller instance.
     * @param $userRepo
     * @param $profileRepo
     * @param $oauthRepo
     * @param $imageRepo
     */
    public function __construct(UserRepository $userRepo, ProfileRepository
                                $profileRepo, OauthRepository $oauthRepo,
                                ImageRepository $imageRepo,
                                UserPrivacyRepository $userPrivacyRepo)
    {
        $this->userRepo = $userRepo;
        $this->profileRepo = $profileRepo;
        $this->oauthRepo = $oauthRepo;
        $this->imageRepo = $imageRepo;
        $this->userPrivacyRepo = $userPrivacyRepo;
    }

    /**
     * Register user
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $user = $this->userRepo->create($request);

        // Inject user into the request so that profile can create a user
        // associated to this user
        $request->merge(['user' => $user]);

        $profile = $this->profileRepo->create($request);

        if($request->has('picture_url')) {
            $request->merge([
                'image_url' => $request['picture_url'],
                'directory' => 'profile_pictures'
            ]);
            $image = $this->imageRepo->create($request);
            $profile->image_id = $image->id;
            $profile->update();
        }
        $this->oauthRepo->create($request);
        $this->userPrivacyRepo->create(['user_id' => $user->id]);

        if($request->is('api/register')) {
            // Send confirmation email
            $name = $profile->first_name . " " . $profile->last_name;
            $infoArray = ['confirmation_code' => $user->confirmation_code];
            $subject = 'Verify your email address';
//          $this->sendEmail('confirmation', $infoArray, $user->email, $name, $subject);
        }
        if($request->is('api/oauth/register')) {
            $json = $request->get('json');
            $oauthType = $request->input('oauth_type');
            $url = OauthController::getUrlFromOauthJSON($json, $oauthType);
            OauthController::updateProfilePicture($this->imageRepo, $profile,
                $url);
        }

        return Response::dataResponse(true, [
            'user' => $user->makeVisible('api_token')
                            ->makeVisible('email')
        ]);
    }

    /**
     * Check if email is taken
     * @param $email
     * @return JsonResponse
     */
    public function emailTaken($email)
    {
        $user = $this->userRepo->findBy('email', $email);
        if($user) {
            $confirmed = $user->confirmation_code == null;
            return Response::dataResponse(true, [
                'taken' => true,
                'confirmed' => $confirmed
            ]);
        }
        else
            return Response::dataResponse(true, [
                'taken' => false
            ]);
    }

    /**
     * Check if username is taken
     * @param $username
     * @return JsonResponse
     */
    public function usernameTaken($username)
    {
        $user = $this->userRepo->findBy('username', $username);
        if($user) {
            $confirmed = $user->confirmation_code == null;
            return Response::dataResponse(true, [
                'taken' => true,
                'confirmed' => $confirmed
            ]);
        }
        else
            return Response::dataResponse(true, [
                'taken' => false
            ]);
    }

    /**
     * Confirm user
     * @param $confirmation_code
     * @return JsonResponse
     */
    public function confirm($confirmation_code)
    {
        if($confirmation_code == null)
            Exceptions::invalidConfirmationCodeException();

        $user = $this->userRepo->findBy('confirmation_code',
            $confirmation_code);

        if($user) {
            $user->update(['confirmation_code' => null]);
            return Response::successResponse(ACCOUNT_VERIFIED);
        }

        Exceptions::invalidConfirmationCodeException();
    }

}
