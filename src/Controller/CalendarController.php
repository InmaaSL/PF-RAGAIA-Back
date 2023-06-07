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

use App\Entity\User;
use App\Entity\CalendarEntry;

use OpenApi\Annotations as OA;
use App\Service\PermissionService;
use App\Service\DtoService;
use App\Service\RestService;

use DateTime;


/**
 * @Route("/api", name="api_")
 */
class CalendarController extends BaseControllerWithExtras
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
     *     "/saveCalendarEntry",
     *     name="Register calendar entry",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving calendar entry"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Calendar entry successfully saved",
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
     *              required={"entry_date", "entry_time" },
     *              @OA\Property(
     *                  property="entry_date",
     *                  description="Date",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="entry_time",
     *                  description="Time",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="all_day",
     *                  description="Is a all day task",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="register_type",
     *                  description="Type of registry",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  description="Title",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  description="Description",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  description="User ID",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="worker",
     *                  description="worker ID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="place",
     *                  description="Place",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="remember",
     *                  description="Is need to be remember",
     *                  type="boolean"
     *              )
     *          )
     *      )
     * )
     * 
     */
    public function saveCalendarEntry(ManagerRegistry $doctrine, Request $request){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $entry_date = $request->get('entry_date');
            $entry_time = $request->get('entry_time');
            $all_day = $request->get('all_day');
            $register_type = $request->get('register_type');
            $title = $request->get('title');
            $description = $request->get('description');
            $user_id = $request->get('user_id');
            $worker_id = $request->get('worker');
            $place = $request->get('place');
            $remember = $request->get('remember');

            date_default_timezone_set('Europe/Madrid'); // Reemplaza 'Europe/Madrid' por la zona horaria correspondiente
            
            $worker = '';

            if($user_id){
                $user = $doctrine->getRepository(User::class)->find($user_id);
            } else {
                $user = null;
            }

            if($worker_id){
                $worker = $doctrine->getRepository(User::class)->find($worker_id);
            } else {
                $worker = null;
            }
            
            $calendarEntry = new CalendarEntry();
            $calendarEntry->setEntryDate(new DateTime($entry_date));
            $calendarEntry->setEntryTime(new DateTime($entry_time));
            $calendarEntry->setAllDay($all_day ? $all_day : '0');
            $calendarEntry->setRegisterType($register_type ? $register_type : '0');
            $calendarEntry->setTitle($title);
            $calendarEntry->setDescription($description);
            $calendarEntry->setUser($user);
            $calendarEntry->setWorker($worker);
            $calendarEntry->setPlace($place);
            $calendarEntry->setRemember($remember ? $remember : '0');

            $em->persist($calendarEntry);
            $em->flush();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $calendarEntry : $message,
        ];

        $groups = ['calendar:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/editCalendarEntry/{calendar_entry_id}",
     *     name="Edit calendar entry",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving calendar entry"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Calendar entry successfully saved",
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
     *              required={"entry_date", "entry_time" },
     *              @OA\Property(
     *                  property="entry_date",
     *                  description="Date",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="entry_time",
     *                  description="Time",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="all_day",
     *                  description="Is a all day task",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="register_type",
     *                  description="Type of registry",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  description="Title",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  description="Description",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  description="User ID",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="worker",
     *                  description="worker ID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="place",
     *                  description="Place",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="remember",
     *                  description="Is need to be remember",
     *                  type="boolean"
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
    public function editCalendarEntry(ManagerRegistry $doctrine, Request $request, $calendar_entry_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $entry_date = $request->get('entry_date');
            $entry_time = $request->get('entry_time');
            $all_day = $request->get('all_day');
            $register_type = $request->get('register_type');
            $title = $request->get('title');
            $description = $request->get('description');
            $user_id = $request->get('user_id');
            $worker_id = $request->get('worker');
            $place = $request->get('place');
            $remember = $request->get('remember');

            date_default_timezone_set('Europe/Madrid'); // Reemplaza 'Europe/Madrid' por la zona horaria correspondiente
            
            $worker = '';

            if($user_id){
                $user = $doctrine->getRepository(User::class)->find($user_id);
            } else {
                $user = null;
            }

            if($worker_id){
                $worker = $doctrine->getRepository(User::class)->find($worker_id);
            } else {
                $worker = null;
            }
            
                if($calendar_entry_id){
                    $calendarEntry = $doctrine->getRepository(CalendarEntry::class)->find($calendar_entry_id);
                    
                    $calendarEntry->setEntryDate(new DateTime($entry_date));
                    $calendarEntry->setEntryTime(new DateTime($entry_time));
                    $calendarEntry->setAllDay($all_day ? $all_day : '0');
                    $calendarEntry->setRegisterType($register_type ? $register_type : '0');
                    $calendarEntry->setTitle($title);
                    $calendarEntry->setDescription($description);
                    $calendarEntry->setUser($user);
                    $calendarEntry->setWorker($worker);
                    $calendarEntry->setPlace($place);
                    $calendarEntry->setRemember($remember ? $remember : '0');

                    $em->persist($calendarEntry);
                    $em->flush();
                }


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $calendarEntry : $message,
        ];

        $groups = ['calendar:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/getCalendarEntry",
     *     name="Get all the calendar entry",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all the calendar entry"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the calendar entry",
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
     */
    public function getCalendarEntry(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $calendarEntries= $doctrine->getRepository(CalendarEntry::class)->findAll();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["calendar:main"];
        return $this->dtoService->getJson($calendarEntries, $groups);
    }

    /**
     * @Route(
     *     "/getSpecificCalendarEntry/{calendar_entry_id}",
     *     name="Get a specific calendar entry",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting the calendar entry"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get the calendar entry",
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
     *     name="calendar_entry_id",
     *     in="path",
     *     required=true,
     *     description="User Id",
     *     @OA\Schema(type="string")
     * )
     *
     */
    public function getSpecificCalendarEntry(ManagerRegistry $doctrine, $calendar_entry_id){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $calendarEntries= $doctrine->getRepository(CalendarEntry::class)->find($calendar_entry_id);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["calendar:main"];
        return $this->dtoService->getJson($calendarEntries, $groups);
    }

        /**
     * @Route(
     *     "/getDayCalendarEntry/{day}",
     *     name="Get a specific day calendar entry",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting the calendar day entry"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get the calendar day entry",
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
     *     name="day",
     *     in="path",
     *     required=true,
     *     description="Day",
     *     @OA\Schema(type="date")
     * )
     *
     */
    public function getDayCalendarEntry(ManagerRegistry $doctrine, $day){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $calendarEntries= $doctrine->getRepository(CalendarEntry::class)->findBy(['entry_date' => new DateTime($day)]);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["calendar:main"];
        return $this->dtoService->getJson($calendarEntries, $groups);
    }


    /**
     * @Route(
     *     "/deleteCalendarEntry/{calendar_entry_id}",
     *     name="Delete calendar entry",
     *     methods={ "DELETE" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting calendar entry"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Calendar entry successfully deleting",
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
     *     name="calendar_entry_id",
     *     in="path",
     *     required=true,
     *     description="Calendar entry Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function deleteCalendarEntry(ManagerRegistry $doctrine, Request $request, $calendar_entry_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $calendarEntry = $doctrine->getRepository(CalendarEntry::class)->find($calendar_entry_id);
        
            $em->remove($calendarEntry);
            $em->flush();


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? 'Registro eliminado' : $message,
        ];

        $groups = ['calendar:main'];
        return $this->dtoService->getJson($response, $groups);
    }
    

}
