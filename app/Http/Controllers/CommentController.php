<?php

namespace App\Http\Controllers;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Repositories\CommentRepository;
use App\Repositories\EntityRepository;
use App\Repositories\EntitysPictureRepository;
use App\Repositories\ImageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * @package App\Http\Controllers
 */
class CommentController extends Controller
{
    private $commentRepo;
    private $entityRepo;
    private $imageRepo;
    private $entitysPictureRepo;

    function __construct(CommentRepository $commentRepo,
                        EntityRepository $entityRepo,
                        ImageRepository $imageRepo,
                        EntitysPictureRepository $entitysPictureRepo)
    {
        $this->commentRepo = $commentRepo;
        $this->entityRepo = $entityRepo;
        $this->imageRepo = $imageRepo;
        $this->entitysPictureRepo = $entitysPictureRepo;
    }

    /**
     * Create a comment
     * @param Request $request, post request
     *        rules: requires comment that is not empty and entity_id
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $entity = $this->entityRepo->create(new Request());
        $user = $request->get('user');

        // Inject user so comment repo can create comment
        $request->merge([
            'entity' => $entity,
            'user' => $user
        ]);

        $comment = $this->commentRepo->create($request);

        $comment->entity_id = $entity->id;
        $comment->on_entity_id = $request->input('on_entity_id');
        $comment->comment = $request->input('comment');

        if($file = $request->file('image')) {
            $request->merge(['image_file' => $file]);
            $image = $this->imageRepo->create($request);

            $request->merge([
                'image' => $image,
                'entity' => $entity
            ]);
            $this->entitysPictureRepo->create($request);
        }

        return Response::dataResponse(true, ['comment' => $comment]);

    }

    /**
     * Return information on comment
     * @param Request $request
     * @param $comment_id
     * @return JsonResponse
     */
    public function read(Request $request, $comment_id)
    {
        if($request->has('fields')) {
            $fields = $request->input('fields');
            $fields = explode(',', $fields);
            $comment = $this->commentRepo->read($comment_id, $fields);
        }
        else
            $comment = $this->commentRepo->read($comment_id);

        if ($comment == null)
            Exceptions::notFoundException(NOT_FOUND);

        return Response::dataResponse(true, ['comment' => $comment]);

    }

    /**
     * Edit comment
     * @param Request $request, post request
     *        rules: requires comment that is not empty
     * @param $comment_id
     * @return JsonResponse
     */
    public function update(Request $request, $comment_id)
    {
        $comment = $this->commentRepo->update($request, $comment_id);

        return Response::dataResponse(true, ['comment' => $comment]);
    }

    /**
     * Delete a comment, only available if comment belongs to logged in user
     * @param Request $request, delete request
     * @param $comment_id
     * @return JsonResponse
     */
    public function delete(Request $request, $comment_id)
    {
        $comment = $this->commentRepo->read($comment_id);
        if ($comment == null)
            Exceptions::notFoundException(NOT_FOUND);

        /* Validate if user deleting is the same as the user from the token */
        $api_token = $comment->user->api_token;
        $headerToken = $request->header('Authorization');
        if ($api_token != $headerToken)
            Exceptions::invalidTokenException(NOT_USERS_OBJECT);

        $entitysPictures = $comment->entity->pictures;

        $request->merge(['directory' => 'images']);
        foreach($entitysPictures as $entitysPicture) {
            $this->imageRepo->delete($request, $entitysPicture->image_id);
        }
        $this->entityRepo->delete($request, $comment->entity_id);

        return Response::successResponse();
    }

}
