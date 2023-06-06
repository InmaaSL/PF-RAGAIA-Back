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
use App\Entity\PayRegister;

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
     *                  property="age_range",
     *                  description="Age Range",
     *                  type="string"
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
            $age_range = $request->get('age_range');
            $age_range = explode(",", $age_range);
            $max_pay = $request->get('max_pay');
            $min_pay = $request->get('min_pay');
            $incentive = $request->get('incentive');
            
            $paidManagement= $doctrine->getRepository(PaidManagement::class)->find($id);
            
            $paidManagement->setAge($age);
            $paidManagement->setMaxPay($max_pay);
            $paidManagement->setMinPay($min_pay);
            $paidManagement->setIncentive($incentive);
            $paidManagement->setAgeRange($age_range);

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

    /**
     * @Route(
     *     "/savePayRegister/{user_id}",
     *     name="Register paid",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving paid"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Paid successfully saved",
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
     *                  property="week_start",
     *                  description="Week start",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="week_end",
     *                  description="Week end",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="base_pay",
     *                  description="base pay",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="max_pay",
     *                  description="max pay",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="percent_measure",
     *                  description="percent measure",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="discount",
     *                  description="discount",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="base_pay_rest",
     *                  description="base pay rest",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="max_incentive",
     *                  description="max incentive",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="incentive",
     *                  description="incentive",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="max_study",
     *                  description="max study",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="study",
     *                  description="study",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="max_bedroom",
     *                  description="max bedroom",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="bedroom",
     *                  description="bedroom",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="total_incentive",
     *                  description="total incentive",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="negative_pay",
     *                  description="negative pay",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="total_pay",
     *                  description="total pay",
     *                  type="float"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="user_id",
     *     in="path",
     *     required=true,
     *     description="Paid management id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function savePayRegister(ManagerRegistry $doctrine, Request $request, $user_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $week_start = $request->get('week_start');
            $week_end = $request->get('week_end');
            $base_pay = $request->get('base_pay');
            $max_pay = $request->get('max_pay');
            $discount = $request->get('discount');
            $percent_measure = $request->get('percent_measure');
            $base_pay_rest = $request->get('base_pay_rest');
            $max_incentive = $request->get('max_incentive');
            $incentive = $request->get('incentive');
            $max_study = $request->get('max_study');
            $study = $request->get('study');
            $max_bedroom = $request->get('max_bedroom');
            $bedroom = $request->get('bedroom');
            $total_incentive = $request->get('total_incentive');
            $negative_pay = $request->get('negative_pay');
            $total_pay = $request->get('total_pay');
            
            $user= $doctrine->getRepository(User::class)->find($user_id);

            $pay = $doctrine->getRepository(PayRegister::class)->findOneBy(['user' => $user, 'week_start' => new DateTime($week_start), 'week_end' => new DateTime($week_end)]);

            if($pay){
                $pay->setBasePay(floatval($base_pay));
                $pay->setMaxPay(floatval($max_pay));
                $pay->setPercentMeasure(floatval($percent_measure));
                $pay->setDiscount(floatval($discount));
                $pay->setBasePayRest(floatval($base_pay_rest));
                $pay->setMaxIncentive(floatval($max_incentive));
                $pay->setIncentive(floatval($incentive));
                $pay->setMaxStudy(floatval($max_study));
                $pay->setStudy(floatval($study));
                $pay->setMaxBedroom(floatval($max_bedroom));
                $pay->setBedroom(floatval($bedroom));
                $pay->setTotalIncentive(floatval($total_incentive));
                $pay->setNegativePay($negative_pay);
                $pay->setTotalPay(floatval($total_pay));
            } else {
                $pay = new PayRegister();
                $pay->setUser($user);
                $pay->setWeekStart(new DateTime($week_start));
                $pay->setWeekEnd(new DateTime($week_end));
                $pay->setBasePay(floatval($base_pay));
                $pay->setMaxPay(floatval($max_pay));
                $pay->setPercentMeasure(floatval($percent_measure));
                $pay->setDiscount(floatval($discount));
                $pay->setBasePayRest(floatval($base_pay_rest));
                $pay->setMaxIncentive(floatval($max_incentive));
                $pay->setIncentive(floatval($incentive));
                $pay->setMaxStudy(floatval($max_study));
                $pay->setStudy(floatval($study));
                $pay->setMaxBedroom(floatval($max_bedroom));
                $pay->setBedroom(floatval($bedroom));
                $pay->setTotalIncentive(floatval($total_incentive));
                $pay->setNegativePay($negative_pay);
                $pay->setTotalPay(floatval($total_pay));
            }
            
            $em->persist($pay);
            $em->flush();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $pay : $message,
        ];

        $groups = ['paidRegister:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/getPayRegisters/{week_start}",
     *     name="Get Register paid",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting pay register"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Pay register successfully getting",
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
     * 
     * @OA\Parameter(
     *     name="week_start",
     *     in="path",
     *     required=true,
     *     description="Pay week start",
     *     @OA\Schema(type="date")
     * )
     * 
     */
    public function getPayRegisters(ManagerRegistry $doctrine, Request $request, $week_start){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $pay = $doctrine->getRepository(PayRegister::class)->findBy(['week_start' => new DateTime($week_start)]);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $pay : $message,
        ];

        $groups = ['paidRegister:main'];
        return $this->dtoService->getJson($response, $groups);
    }


}
