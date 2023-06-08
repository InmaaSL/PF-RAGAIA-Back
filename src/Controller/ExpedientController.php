<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;

use App\Entity\User;
use App\Entity\Expedient;

use OpenApi\Annotations as OA;

use App\Service\DtoService;
use App\Service\RestService;
use App\Service\FileUploader;
use App\Service\ExpedientService;

use DateTime;

/**
 * @Route("/api", name="api_")
 */
class ExpedientController extends BaseControllerWithExtras
{
    private $expedientService;


    /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     * @param ExpedientService $expedientService
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService,
        ExpedientService $expedientService,
        // PermissionService $permissionSvc,
        ) {
        parent::__construct($restService, $dtoSvc);
        $this->restService = $restService;
        $this->expedientService = $expedientService;
        $this->dtoService = $dtoSvc;
    }

    /**
     * @Route(
     *     "/setExpedientDocument/{user_id}",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error user expedient"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User expedient successfully setted",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="error", type="string", example="false"),
     *         @OA\Property(property="data", type="integer", example=0),
     *         @OA\Property(property="message", type="string", example="Error explanation")
     *     )
     * )
     *
     * @OA\Response(
     *     response="401",
     *     description="Invalid token",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="401"),
     *         @OA\Property(property="message", type="string")
     *     )
     * )
     *
     * @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="application/pdf",
     *          @OA\Schema(
     *              type="object",
     *              required={"document"},
     *              @OA\Property(
     *                  property="document",
     *                  description="The user document",
     *                  type="string",
     *                  format="binary"
     *              ),
     *              @OA\Property(
     *                  property="file_name",
     *                  description="File name",
     *                  type="string"
     *              ),
     *          )
     *      )
     * )
     *
     * @OA\Parameter(
     *     name="user_id",
     *     in="path",
     *     required=true,
     *     description="User Id",
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="UserData")
     */
    public function setExpedientDocument(ManagerRegistry $doctrine, Request $request, string $uploadDir, FileUploader $uploader, $user_id)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $request->files->get('document');
            $file_name = $request->get('file_name');

            $user = $em->getRepository(User::class)->find($user_id);
            if($user instanceof User)
            {

                if ($user instanceof User) {

                    $name = md5(uniqid());
                    $up = $uploadDir;

                    if(!is_dir($up))
                    {
                        mkdir($up, 0755, true);
                    }

                    $up = $uploadDir . '/user'.$user_id.'/expedient';

                    // $files = glob($up.'/*');
                    // foreach ($files as $file){
                    //     if(is_file($file)){
                    //         unlink($file);
                    //     }
                    // }
                    
                    $uploader->uploadDocument($up, $document, $name);

                    $expedient = new Expedient();
                    $expedient->setUser($user);
                    $expedient->setDate(new \DateTime());
                    $expedient->setFile($name);
                    $expedient->setNameFile($file_name);

                    $em->persist($expedient);
                    $em->flush();
                }

                else
                {
                    $code = 500;
                    $error = true;
                    $message = "UserData not found";
                }
            }
            else
            {
                $code = 500;
                $error = true;
                $message = "There is no user with id: " . $user_id;
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? 'Document uploaded' : $message,
        ];

        return $this->dtoService->getJson($response);
    }

    /**
     * @Route(
     *     "/v2/getAllUserExpedientDocument/{id}",
     *     name="Get all expedient document paginated",
     *     methods={ "GET" },
     * )
     * 
     * * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="User id",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="c",
     *     in="query",
     *     required=false,
     *     description="Count",
     *     @OA\Schema(type="string")
     * )
     * 
     * * @OA\Parameter(
     *     name="p",
     *     in="query",
     *     required=false,
     *     description="Page",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="s",
     *     in="query",
     *     required=false,
     *     description="Sort",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="f",
     *     in="query",
     *     required=false,
     *     description="Filter",
     *     @OA\Schema(type="string")
     * )
     */
    public function getAllUserExpedientDocumentPaginated(Request $request, $id) {
        $group = ["expedient:main"];
        
        $dataRequested = $this->restService->getRequestedData($request);
        $document = $this->expedientService->get($dataRequested);
        return $this->dtoService->getJson($document,$group);
    }


    /**
     * @Route(
     *     "/getExpedientDocument/{document_id}",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting user expedient document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User expedient document retrieved",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="data", type="object",
     *                  @OA\Property(property="profile_pic", type="string"),
     *           )
     *     )
     * )
     *
     * @OA\Response(
     *     response="401",
     *     description="Authentication error",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="401"),
     *         @OA\Property(property="message", type="string")
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="document_id",
     *     in="path",
     *     required=true,
     *     description="Document Id",
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="UserData")
     */
    public function getExpedientDocument(ManagerRegistry $doctrine, Request $request, $document_id)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $em->getRepository(Expedient::class)->find($document_id);

            if($document instanceof Expedient)
            {
                $user_id = $document->getUser()->getId();
                $userDocument = $document->getFile();
                if($userDocument == null){
                    $url_document = null;
                }else{
                    $url_document = '/uploads/user'.$user_id.'/expedient/'.$userDocument;
                }
            }
            else
            {
                $url_document = null;
                $code = 500;
                $error = true;
                $message = "There is no document with id: " . $document_id;
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get the user data - Error: {$ex->getMessage()}";
        }

        $response = [
            'url' => $url_document,
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $document : $message,
        ];

        $group = ["expedient:main"];
        return $this->dtoService->getJson($response, $group);
    }

    /**
     * @Route(
     *     "/deleteExpedientDocument/{document_id}",
     *     methods={ "DELETE" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user expedient document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Expedient document deleted",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="data", type="object",
     *                  @OA\Property(property="profile_pic", type="string"),
     *           )
     *     )
     * )
     *
     * @OA\Response(
     *     response="401",
     *     description="Authentication error",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="401"),
     *         @OA\Property(property="message", type="string")
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="document_id",
     *     in="path",
     *     required=true,
     *     description="Document Id",
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="UserData")
     */
    public function deleteExpedientDocument(ManagerRegistry $doctrine, Request $request, $document_id, $uploadDir)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $em->getRepository(Expedient::class)->find($document_id);

            if($document instanceof Expedient)
            {

                $user_id = $document->getUser()->getId();
                $userDocument = $document->getFile();

                if($userDocument == null){
                    $url_document = null;
                }else{
                    $url_document = $uploadDir . '/user'.$user_id.'/expedient/'.$userDocument;
                }

                if(is_file($url_document)){
                    unlink($url_document);
                }

                $em->remove($document);
                $em->flush();
            }
            else
            {
                $url_document = null;
                $code = 500;
                $error = true;
                $message = "There is no document with id: " . $document_id;
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get the user data - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $url_document : $message,
        ];

        return $this->dtoService->getJson($response);
    }


    /**
     * @Route(
     *     "/v2/expedientSpecial",
     *     name="expedient special",
     *     methods={ "GET" },
     * )
     */
    public function getUsers() {
        $group = ["expedient:main"];

        $users = $this->userService->getAll();
        return $this->dtoService->getJson($users,$group);
    }

    /**
     * @Route(
     *     "/user/profile_pic",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error specific user profile pic"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User profile pic successfully setted",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="error", type="string", example="false"),
     *         @OA\Property(property="data", type="integer", example=0),
     *         @OA\Property(property="message", type="string", example="Error explanation")
     *     )
     * )
     *
     * @OA\Response(
     *     response="401",
     *     description="Invalid token",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="401"),
     *         @OA\Property(property="message", type="string")
     *     )
     * )
     *
     * @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="image/png",
     *          @OA\Schema(
     *              type="object",
     *              required={"profile_pic"},
     *              @OA\Property(
     *                  property="profile_pic",
     *                  description="The user profile_pic",
     *                  type="string",
     *                  format="binary"
     *              ),
     *          )
     *      )
     * )
     *
     *
     * @OA\Tag(name="UserData")
     */
    public function setSpecificUserProfilePic(Request $request, string $uploadDir, FileUploader $uploader)
    {
        $serializer = $this->serializer;
        $em = $this->getDoctrine()->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $profile_pic = $request->files->get('picture');

            if($user instanceof User)
            {
                $userData = $this->getUser()->getUserData();
                $user = $this->getUser;

                if ($userData instanceof UserData) {

                    $name = md5(uniqid());
                    $up = $uploadDir;

                    if(!is_dir($up))
                    {
                        mkdir($up, 0755, true);
                    }

                    $up = $uploadDir . '/user'.$this->getUser()->getId().'/profile_pictures';

                    $files = glob($up.'/*');

                    foreach ($files as $file){
                        if(is_file($file)){
                            unlink($file);
                        }
                    }

                    $uploader->uploadImage($up, $profile_pic, $name);


                    $expedient = new Expedient();
                    $expedient->setUser($user);
                    $expedient->setName($name);
                    $expedient->setDate(new \DateTime());
                    $expedient->setFile($name);

                    $em->persist($expedient);
                    $em->flush();

                    // $userData->setProfilePic($name);
                    // $em->persist($userData);
                    // $em->flush();
                }

                else
                {
                    $code = 500;
                    $error = true;
                    $message = "UserData not found";
                }
            }
            else
            {
                $code = 500;
                $error = true;
                $message = "There is no user with id: " . $user_id;
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? 'picture uploaded' : $message,
        ];

        return new Response($serializer->serialize($response, "json", SerializationContext::create()->enableMaxDepthChecks()));
    }

}