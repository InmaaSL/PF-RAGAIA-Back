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
use App\Entity\UserData;
use App\Entity\Centre;
use App\Entity\ProfessionalCategory;
use App\Controller\Exception;
use App\Entity\UserProfessionalCategoryCentre;
use App\Repository\UserProfessionalCategoryCentreRepository;
use OpenApi\Annotations as OA;

use App\Service\DtoService;
use App\Service\RestService;
use App\Service\UserService;
/**
 * @Route("/api", name="api_")
 */
class UserController extends BaseControllerWithExtras
{

    private $userService;
    private $userProfessionalCategoryCentreRepository;
    /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     * @param UserService $userService
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService,
        UserService $userService,
        UserProfessionalCategoryCentreRepository $userProfessionalCategoryCentreRepository
        // PermissionService $permissionSvc,
        ) {
        parent::__construct($restService, $dtoSvc);
        $this->restService = $restService;
        $this->userService = $userService;
        $this->dtoService = $dtoSvc;
        $this->userProfessionalCategoryCentreRepository = $userProfessionalCategoryCentreRepository;
    }

    /**
     * @Route(
     *     "/user/getUserData",
     *     name="Get my user info",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting current user info"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Current user info retrieved",
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
    public function getUserInfo(){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $user = $this->getUser();
            $userData = $user->getUserData();

            $ar = array(
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "name" => $userData->getName(),
                "surname" => $userData->getSurname(),
                "dni" => $userData->getDni(),
                "roles" => $user->getRoles(),
                "phone" => $userData->getPhone(),
                "address" => $userData->getAddress(),
                "town" => $userData->getTown(),
                "province" => $userData->getProvince(),
                "postal_code" => $userData->getPostalCode(),
                "birth_date" => $userData->getBirthDate(),
                "admission_date" => $userData->getAdmissionDate(),
                "custody" => $userData->getCustody() ? $userData->getCustody()->getId() : '' ,
            );

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $ar : $message,
        ];

        $groups = ["user:main"];
        return $this->dtoService->getJson($response, $groups);

    }

    /**
     * @Route(
     *     "/user/getUserId",
     *     name="Get my ID",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting current user id"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Current user id retrieved",
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
    public function getCurrentUserId(){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $id = $this->getUser()->getId();
            if($id) {
                $ar = array(
                    "id" => $id
                );
            } else {
                    $ar = null;
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get the user id - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $ar : $message,
        ];

        return $this->dtoService->getJson($response);
    }

    /**
     * @Route(
     *     "/user/userData/{user_id}",
     *     name="Get userData info by user id",
     *     methods={ "GET" },
     * )
     * 
     * @Route(
     *     "/user/userData",
     *     name="getMeUserData",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting user data"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User data retrieved",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
     *         @OA\Property(property="data", type="object",
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
     *     name="user_id",
     *     in="path",
     *     required=false,
     *     description="User Id",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="UserData")
     */
    public function getUserData(ManagerRegistry $doctrine, $user_id=null){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();
        $repository = $doctrine->getRepository(User::class);
        
        $message = "";

        try {
            $code = 200;
            $error = false;

            if($user_id)
                $user = $repository->find($user_id);
            else
                $user = $this->getUser();
            if($user instanceof User)
            {
                $userData = $user->getUserData();
                if($userData)
                {
                    $ar = array(
                        "userDataID" => $userData->getId() ? $userData->getId() : '',
                        "roles" => $user->getRoles() ? $user->getRoles() : '',
                        "name" => $userData->getName() ? $userData->getName() : '',
                        "surname" => $userData->getSurname() ? $userData->getSurname() : '',
                        "dni" => $userData->getDni() ? $userData->getDni() : '',
                        "email" => $userData->getEmail() ? $userData->getEmail() : $userData->getUser()->getEmail(),
                        "phone" => $userData->getPhone() ? $userData->getPhone() : '',
                        "address" => $userData->getAddress() ? $userData->getAddress() : '',
                        "town" => $userData->getTown() ? $userData->getTown() : '',
                        "province" => $userData->getProvince() ? $userData->getProvince() : '',
                        "postal_code" => $userData->getPostalCode() ? $userData->getPostalCode() : '',
                        "birth_date" => $userData->getBirthDate() ? $userData->getBirthDate() : '',
                        "admission_date" => $userData->getAdmissionDate() ? $userData->getAdmissionDate() : '',
                        "custody" => $userData->getCustody() ? $userData->getCustody()->getId() : '',
                    );
                }
                else
                {
                    $ar = null;
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
            $message = "An error has occurred trying to get the user data - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $ar : $message,
        ];

        $groups = ["user:main"];
        return $this->dtoService->getJson($ar, $groups);
    }

    /**
     * @Route(
     *     "/user/usersData",
     *     name="Get all users data",
     *     methods={ "GET" },
     * )
     * 
     * @Route(
     *     "/user/usersData/{role}",
     *     name="Get users data by role",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all users data"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Users data retrieved",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
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
     *      name="role",
     *      in="path",
     *      description="User role to filter",
     *      required=false,
     *      @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="UserData")
     */
    public function getUsersData(ManagerRegistry $doctrine, Request $request, $role = null){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();
        $repositoryUser = $doctrine->getRepository(User::class);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $users = [];

            if($role){
                if($role == 'superadmin'){
                    $role = "ROLE_SUPERADMIN";
                } else if ($role == 'worker') {
                    $role = "ROLE_WORKER";
                } else if($role == 'direct'){
                    $role = "ROLE_DIRECT_ACTION";
                } else if($role == 'edusos'){
                    $role = "ROLE_EDUSOS_TICS_MEDIADORES";
                } else if($role == 'psycho'){
                    $role = "ROLE_PSYCHOLOGIST";
                } else if($role == 'social'){
                    $role = "ROLE_SOCIAL_WORKER";
                } else if($role == 'domestic'){
                    $role = "DOMESTIC_SUPPORT";
                } else if($role == 'management'){
                    $role = "ROLE_MANAGEMENT";
                } else if($role == 'nna'){
                    $role = "ROLE_NNA";
                }

                $uUsers = $repositoryUser->getAllByRole($role);
    
                foreach($uUsers as $us)
                {
                    if($us instanceof User)
                    {
                        if($us->getUserData()){
                            $ar = array(
                                "id" => $us->getId(),
                                "email" => $us->getEmail() ? $us->getEmail() : '',
                                "roles" => $us->getRoles() ? $us->getRoles() : '',
                                "name" => $us->getUserData()->getName() ? $us->getUserData()->getName() : '',
                                "surname" => $us->getUserData()->getSurname() ? $us->getUserData()->getSurname() : '',
                                "phone" => $us->getUserData()->getPhone() ? $us->getUserData()->getPhone() : '',
                                "address" => $us->getUserData()->getAddress() ? $us->getUserData()->getAddress() : '',
                                "town" => $us->getUserData()->getTown() ? $us->getUserData()->getTown() : '',
                                "province" => $us->getUserData()->getProvince() ? $us->getUserData()->getProvince() : '',
                                "postal_code" => $us->getUserData()->getPostalCode() ? $us->getUserData()->getPostalCode() : '',
                                "birth_date" => $us->getUserData()->getBirthDate() ? $us->getUserData()->getBirthDate() : '',
                                "admission_date" => $us->getUserData()->getAdmissionDate() ? $us->getUserData()->getAdmissionDate() : '',
                                "custody" => $us->getUserData()->getCustody() ? $us->getUserData()->getCustody()->getId() : ''
                            );
                            $users[] = $ar;
                        }
                    }
                }
            } else {
                $uUsers = $repositoryUser->findAll();
    
                foreach($uUsers as $us)
                {
                    if($us instanceof User)
                    {
                        if($us->getUserData()){
                            $ar = array(
                                "id" => $us->getId(),
                                "email" => $us->getEmail() ? $us->getEmail() : '',
                                "roles" => $us->getRoles() ? $us->getRoles() : '',
                                "name" => $us->getUserData()->getName() ? $us->getUserData()->getName() : '',
                                "surname" => $us->getUserData()->getSurname() ? $us->getUserData()->getSurname() : '',
                                "phone" => $us->getUserData()->getPhone() ? $us->getUserData()->getPhone() : '',
                                "address" => $us->getUserData()->getAddress() ? $us->getUserData()->getAddress() : '',
                                "town" => $us->getUserData()->getTown() ? $us->getUserData()->getTown() : '',
                                "province" => $us->getUserData()->getProvince() ? $us->getUserData()->getProvince() : '',
                                "postal_code" => $us->getUserData()->getPostalCode() ? $us->getUserData()->getPostalCode() : ''                ,
                                "birth_date" => $us->getUserData()->getBirthDate() ? $us->getUserData()->getBirthDate() : '',
                                "admission_date" => $us->getUserData()->getAdmissionDate() ? $us->getUserData()->getAdmissionDate() : '',
                                "custody" => $us->getUserData()->getCustody() ? $us->getUserData()->getCustody()->getId() : ''
                            );
                            $users[] = $ar;
                        }
                    }
                }
    
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get the user data - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $users : $message,
        ];

        $groups = ["user:main"];
        return $this->dtoService->getJson($response, $groups);

    }

    /**
     * @Route(
     *     "/user/delete/{id}",
     *     name="Delete user by id",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="user was successfully removed",
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
     *     description="user Id",
     *     @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="User")
     */
    
    public function removeUser(ManagerRegistry $doctrine, $id){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();
        $repository = $doctrine->getRepository(User::class);

        $message = "";
        
        try {
            $code = 200;
            $error = false;

            $user = $repository->find($id);

            if(!$user instanceof User)
            {
                $code = 500;
                $error = true;
                $message = "There is no user with id: " . $id;
            }
            else
            {
                try {
                    $user->setDeleted(true);
                    $em->flush();

                }
                catch(\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
                    $code = 500;
                    $error = true;
                    $message = "Error clave ajena - Error: {$e->getMessage()}";
                }
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to delete the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? "Deleted" : $message,
        ];

        return $this->dtoService->getJson($response);
    }

    /**
     * @Route(
     *     "/getProfessionalCategories",
     *     name="Get all the professional categories",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all the professional categories"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the professional categories",
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
    public function getProfessionalCategories(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $professionalCategoriesArray = [];

            $professionalCategories = $doctrine->getRepository(ProfessionalCategory::class)->findAll();

            foreach($professionalCategories as $pc)
            {
                if($pc instanceof ProfessionalCategory)
                {
                    $ar = array(
                        "id" => $pc->getId(),
                        "name" => $pc->getName() ? $pc->getName() : '',
                    );
                    $professionalCategoriesArray[] = $ar;
                }
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["user:main"];
        return $this->dtoService->getJson($professionalCategoriesArray, $groups);
    }

    /**
     * @Route(
     *     "/getCentres",
     *     name="Get all centres",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all centres"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all centres",
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
    public function getCentres(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $centresArray = [];

            $centres = $doctrine->getRepository(Centre::class)->findAll();

            foreach($centres as $c)
            {
                if($c instanceof Centre)
                {
                    $ar = array(
                        "id" => $c->getId(),
                        "name" => $c->getName() ? $c->getName() : '',
                    );
                    $centresArray[] = $ar;
                }
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["user:main"];
        return $this->dtoService->getJson($centresArray, $groups);

    }

    /**
     * @Route(
     *     "/v2/user",
     *     name="users",
     *     methods={ "GET" },
     * )
     */
    public function getUsers() {
        $group = ["user:main"];

        $users = $this->userService->getAll();
        return $this->dtoService->getJson($users,$group);
    }

    /**
     * @Route(
     *     "/v2/worker",
     *     name="workers",
     *     methods={ "GET" },
     * )
     */
    public function getWorkers() {
        $group = ["user:main"];

        $users = $this->userService->getAllWorkers();
        return $this->dtoService->getJson($users,$group);
    }

    /**
     * @Route(
     *     "/v2/nna/{id}",
     *     name="nns",
     *     methods={ "GET" },
     * )
     * @OA\Parameter(
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
    public function getAllNNA(Request $request, $id) {
        $group = ["user:main"];

        $dataRequested = $this->restService->getRequestedData($request);
        $users = $this->userService->getAllNNA($dataRequested);
        return $this->dtoService->getJson($users,$group);
    }

    /**
     * @Route(
     *     "/v2/user/{id}",
     *     name="userInfo",
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
    public function getUsersPaginated(Request $request, $id) {
        $group = ["user:main"];
        
        $dataRequested = $this->restService->getRequestedData($request);
        $user = $this->userService->get($dataRequested);
        return $this->dtoService->getJson($user,$group);
    }

    /**
     * @Route(
     *     "/setUserCentre/{user_id}",
     *     name="Establish a user’s work centre ",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving user data"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User data successfully saved",
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
     *              required={"centreId"},
     *              @OA\Property(
     *                  property="centreId",
     *                  description="Centre ID",
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
    public function setUserCentre(ManagerRegistry $doctrine, Request $request, $user_id)
    {
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        try {
            $code = 200;
            $error = false;
            
            $user = $doctrine->getRepository(User::class)->find($user_id);

            $centreId = $request->get('centreId');
            $centre = $doctrine->getRepository(Centre::class)->find($centreId);

            if($user instanceof User && $centre instanceof Centre)
            {
                //Buscamos si este usuario ya está registrado en este centro: 
                $searchUserCentre = $doctrine->getRepository(UserProfessionalCategoryCentre::class)->findBy(['user' => $user_id, 'centre' => $centreId]);

                if(!$searchUserCentre){
                    $newUserCentreRegistry = new UserProfessionalCategoryCentre();
                    $newUserCentreRegistry->setUser($user);
                    $newUserCentreRegistry->setCentre($centre);
                    $em->persist($newUserCentreRegistry);
                    $em->flush();
                } else {
                    foreach ($searchUserCentre as $key) {
                        $newUserCentreRegistry = $key;
                    }
                }
            }
            else
            {
                $code = 500;
                $error = true;
                $message = 'The centre ID does not exits';
            }       

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $newUserCentreRegistry : $message,
        ];

        $groups = ['user:cpc'];
        return $this->dtoService->getJson($newUserCentreRegistry, $groups);
    }

    /**
     * @Route(
     *     "/setUserProfessionalCategoryCentre/{user_id}",
     *     name="Establish a user’s work centre and professional category",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving user data"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User data successfully saved",
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
     *              required={"centreId"},
     *              @OA\Property(
     *                  property="centreId",
     *                  description="Centre ID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="professionalCategoryId",
     *                  description="Professional category ID",
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
    public function setUserProfessionalCategoryCentre(ManagerRegistry $doctrine, Request $request, $user_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        try {
            $code = 200;
            $error = false;
            
            $user = $doctrine->getRepository(User::class)->find($user_id);

            $centreId = $request->get('centreId');
            $centre = $doctrine->getRepository(Centre::class)->find($centreId);

            $professionalCategoryId = $request->get('professionalCategoryId');
            $professionalCategory = $doctrine->getRepository(ProfessionalCategory::class)->find($professionalCategoryId);

            if($user instanceof User && $centre instanceof Centre && $professionalCategory instanceof ProfessionalCategory)
            {
                //Buscamos si este usuario ya esta registrado en este centro: 
                $searchUserCentre = $doctrine->getRepository(UserProfessionalCategoryCentre::class)->findBy(['user' => $user_id, 'centre' => $centreId]);

                if(!$searchUserCentre){
                    $newUserCentreRegistry = new UserProfessionalCategoryCentre();
                    $newUserCentreRegistry->setUser($user);
                    $newUserCentreRegistry->setCentre($centre);
                    $newUserCentreRegistry->setProfessionalCategory($professionalCategory);
                    $em->persist($newUserCentreRegistry);
                } else {
                    //Si está registrado en el centro comprobamos que categoría profesional tiene: 
                    foreach ($searchUserCentre as $key) {
                        if(!$key->getProfessionalCategory() || $key->getProfessionalCategory()->getId() != $professionalCategoryId){
                            //Si la categoria profesional es diferente a la que ha seleccionado ahora cambiamos la categoria del registro.
                            $key->setProfessionalCategory($professionalCategory);
                            $em->persist($key);
                            $newUserCentreRegistry = $key;
                        }
                    }
                }
                $em->flush();
            }
            else
            {
                $code = 500;
                $error = true;
                $message = 'The centre ID does not exits';
            }       

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $newUserCentreRegistry : $message,
        ];

        $groups = ['user:cpc'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/updateUserProfessionalCategoryCentre/{register_id}",
     *     name="Update a user’s work centre and professional category",
     *     methods={ "POST" },
     * )
     *
     * 
     * @OA\Response(
     *     response=500,
     *     description="Error saving user data"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User data successfully saved",
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
     *              required={"userId, centreId, professionalCategoryId"},
     *              @OA\Property(
     *                  property="userId",
     *                  description="User ID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="centreId",
     *                  description="Centre ID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="professionalCategoryId",
     *                  description="Professional category ID",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="register_id",
     *     in="path",
     *     required=true,
     *     description="User Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function updateUserProfessionalCategoryCentre(ManagerRegistry $doctrine, Request $request, $register_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        try {
            $code = 200;
            $error = false;
            
            $userId = $request->get('userId');
            $user = $doctrine->getRepository(User::class)->find($userId);

            $centreId = $request->get('centreId');
            $centre = $doctrine->getRepository(Centre::class)->find($centreId);

            $professionalCategoryId = $request->get('professionalCategoryId');
            $professionalCategory = $doctrine->getRepository(ProfessionalCategory::class)->find($professionalCategoryId);

            if($user instanceof User && $centre instanceof Centre)
            {
                
                $searchUserCentre = $doctrine->getRepository(UserProfessionalCategoryCentre::class)->find($register_id);
                if($searchUserCentre){
                    $searchUserCentre->setUser($user);
                    $searchUserCentre->setCentre($centre);
                    $searchUserCentre->setProfessionalCategory($professionalCategory);
                    
                    $em->persist($searchUserCentre);
                    $em->flush();
                } else {
                    $code = 500;
                    $error = true;
                    $message = 'Something was wrong';    
                }
            }
            else
            {
                $code = 500;
                $error = true;
                $message = 'Something was wrong';
            }       

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $searchUserCentre : $message,
        ];

        $groups = ['user:cpc'];
        return $this->dtoService->getJson($searchUserCentre, $groups);
    }


    /**
     * @Route(
     *     "/user/getUserCentreProfessionalCategory/{user_id}",
     *     name="Get all user centres and professional categories",
     *     methods={ "GET" },
     * )
     * 
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all user centres"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="All user centres retrieved",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="code", type="integer", example="200"),
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
     *      name="user_id",
     *      in="path",
     *      description="User id to filter",
     *      required=false,
     *      @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="UserData")
     */
    public function getUserCentreProfessionalCategory(ManagerRegistry $doctrine, Request $request, $user_id){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();
        
        $message = "";
        
        try {
            $code = 200;
            $error = false;
            
            $registry = [];
            $userCentres = $doctrine->getRepository(UserProfessionalCategoryCentre::class)->findBy(['user' => $user_id]);
    
            foreach($userCentres as $us)
            {
                if($us instanceof UserProfessionalCategoryCentre)
                {
                    $ar = array(
                        "id" => $us->getId(),
                        "centre" => $us->getCentre() ? $us->getCentre() : '',
                        "professionalCategory" => $us->getProfessionalCategory() ? $us->getProfessionalCategory() : ''
                    );
                    $registry[] = $ar;
                }
            }
    
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get the user data - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $registry : $message,
        ];

        $groups = ["userProfessionalCategoryCentre:main"];
        return $this->dtoService->getJson($registry, $groups);
    }

    /**
     * @Route(
     *     "/deleteUserProfessionalCategoryCentre/{register_id}",
     *     name="Delete a user’s work centre and professional category",
     *     methods={ "DELETE" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user data"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User data successfully deleted",
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
     *     name="register_id",
     *     in="path",
     *     required=true,
     *     description="Register Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function deleteUserProfessionalCategoryCentre(ManagerRegistry $doctrine, Request $request, $register_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        try {
            $code = 200;
            $error = false;
            
            //Buscamos el registro: 
            $searchUserCentre = $doctrine->getRepository(UserProfessionalCategoryCentre::class)->find($register_id);

            if($searchUserCentre){
                $em->remove($searchUserCentre);
                $em->flush();
                $message = "Deleted record";        
            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to delete de record - Error: {$ex->getMessage()}";        
            }
        
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to delete de record - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        $groups = ['user:cpc'];
        return $this->dtoService->getJson($response, $groups);
    }

}
