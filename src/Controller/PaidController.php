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

use App\Entity\User;
use App\Entity\PaidManagement;

use OpenApi\Annotations as OA;

use App\Service\DtoService;
use App\Service\RestService;
use App\Service\PermissionService;
use DateTime;


/**
 * @Route("/api", name="api_")
 */
class PaidController extends BaseControllerWithExtras
{
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
     *     "/getPaidManagement",
     *     name="Get all the paid management",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all the paid management"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the paid management",
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
    public function getPaidManagements(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $paidManagement= $doctrine->getRepository(PaidManagement::class)->findAll();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["paidManagement:main"];
        return $this->dtoService->getJson($paidManagement, $groups);
    }

    /**
     * @Route(
     *     "/getPaidManagement/{id}",
     *     name="Get the paid management",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting the paid management"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get the paid management",
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
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="Paid management id",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="User")
     */
    public function getPaidManagement(ManagerRegistry $doctrine, $id){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $paidManagement= $doctrine->getRepository(PaidManagement::class)->find($id);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["paidManagement:main"];
        return $this->dtoService->getJson($paidManagement, $groups);
    }

    /**
     * @Route(
     *     "/savePaidManagement/{id}",
     *     name="Register paid management",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving paid management"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Paid management successfully saved",
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
     *              required={"age", "max_pay", "min_pay", "incentive" },
     *              @OA\Property(
     *                  property="age",
     *                  description="Age",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="max_pay",
     *                  description="Max pay",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="min_pay",
     *                  description="Min pay",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="incentive",
     *                  description="Incentive",
     *                  type="float"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="Paid management id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function savePaidManagement(ManagerRegistry $doctrine, Request $request, $id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $age = $request->get('age');
            $max_pay = $request->get('max_pay');
            $min_pay = $request->get('min_pay');
            $incentive = $request->get('incentive');
            
            $paidManagement= $doctrine->getRepository(PaidManagement::class)->find($id);
            
            $paidManagement->setAge($age);
            $paidManagement->setMaxPay($max_pay);
            $paidManagement->setMinPay($min_pay);
            $paidManagement->setIncentive($incentive);

            $em->persist($paidManagement);
            $em->flush();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $paidManagement : $message,
        ];

        $groups = ['paidManagement:main'];
        return $this->dtoService->getJson($response, $groups);
    }






}
