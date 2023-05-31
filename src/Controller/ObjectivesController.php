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
use Symfony\Component\Security\Core\Security;
use App\Controller\Exception;

use OpenApi\Annotations as OA;

use App\Entity\User;
use App\Entity\ObjectiveType;
use App\Entity\Objective;

use App\Service\PermissionService;
use App\Service\DtoService;
use App\Service\RestService;
use App\Service\FileUploader;

use DateTime;

/**
 * @Route("/api", name="api_")
 */
class ObjectivesController extends BaseControllerWithExtras
{
    private $educationDocumentService;
    private $educationRecordService;

    /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     * 
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService
        ) {
        parent::__construct($restService, $dtoSvc);
        $this->restService = $restService;
        $this->dtoService = $dtoSvc;
    }

    /**
     * @Route(
     *     "/getObjectiveType",
     *     name="Get all the objectives type",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all the objectives type"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the objctives types",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="data", type="object",
     *                  @OA\Property(property="userDataID", type="integer"),
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
     * @OA\Tag(name="User")
     */
    public function getObjectiveType(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $objectiveTypes= $doctrine->getRepository(ObjectiveType::class)->findAll();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["objective_type:main"];
        return $this->dtoService->getJson($objectiveTypes, $groups);
    }


    /**
     * @Route(
     *     "/saveObjective/{user_id}",
     *     name="Register objective",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving objective data"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Objective data successfully saved",
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
     *          mediaType="application/x-www-form-urlencoded",
     *          @OA\Schema(
     *              type="object",
     *              required={"year", "month", "type", "need_detected", "objective"},
     *              @OA\Property(
     *                  property="year",
     *                  description="Objective year",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="month",
     *                  description="Objective month",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  description="Objective type",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="need_detected",
     *                  description="Objective need detected",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="objective",
     *                  description="Objective",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="valuation",
     *                  description="Objective valuation",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="indicator",
     *                  description="Objective indicator",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="comment",
     *                  description="Objective comment",
     *                  type="string"
     *              )
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
     */
    public function registerObjective(ManagerRegistry $doctrine, Request $request, $user_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $year = $request->get('year');
            $month = $request->get('month');
            $type_id = $request->get('type');
            $need_detected = $request->get('need_detected');
            $objectiveM = $request->get('objective');
            $valuation = $request->get('valuation');
            $indicator = $request->get('indicator');
            $comment = $request->get('comment');

            $user = $doctrine->getRepository(User::class)->find($user_id);
            
            if($user instanceof User)
            {

                $objectiveType = $doctrine->getRepository(ObjectiveType::class)->find($type_id);
                $objective = $doctrine->getRepository(Objective::class)->findOneBy(['user' => $user, 'year' => $year, 'month' => $month, 'type' => $type_id]);

                if($objective){
                    $objective->setNeedDetected($need_detected);
                    $objective->setObjective($objectiveM);
                    $objective->setIndicator($indicator ? $indicator : '');
                    $objective->setValuation($valuation ? $valuation : '');
                    $objective->setComment($comment ? $comment : '');

                } else {
    
                    $objective = new Objective();
                    $objective->setUser($user);
                    $objective->setType($objectiveType);
                    $objective->setMonth($month);
                    $objective->setYear($year);
                    $objective->setNeedDetected($need_detected);
                    $objective->setObjective($objectiveM);
                    $objective->setIndicator($indicator ? $indicator : '');
                    $objective->setValuation($valuation ? $valuation : '');
                    $objective->setComment($comment ? $comment : '');

                }

                $em->persist($objective);
                $em->flush();

            }
            else
            {
                $code = 500;
                $error = true;
                $message = 'The user ID does not exits';
            }       

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $objective : $message,
        ];

        $groups = ['objective:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/getObjectives/{user_id}",
     *     name="Get objectives",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting objectives"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the objectives",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="data", type="object",
     *                  @OA\Property(property="userDataID", type="integer"),
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
     * @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="application/x-www-form-urlencoded",
     *          @OA\Schema(
     *              type="object",
     *              required={"year", "month"},
     *              @OA\Property(
     *                  property="year",
     *                  description="Objective year",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="month",
     *                  description="Objective month",
     *                  type="string"
     *              )
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

     * @OA\Tag(name="User")
     */
    public function getObjectives(ManagerRegistry $doctrine, Request $request, $user_id){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $year = $request->get('year');
            $month = $request->get('month');
            $user = $doctrine->getRepository(User::class)->find($user_id);

            $objectives = $doctrine->getRepository(Objective::class)->findBy(['user' => $user, 'year' => $year, 'month' => $month]);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["objective:main"];
        return $this->dtoService->getJson($objectives, $groups);
    }




}
