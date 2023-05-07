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

use OpenApi\Annotations as OA;

/**
 * @Route("/api", name="api_")
 */
class UserController extends AbstractController
{
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
    public function getUserInfo()
    {
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
                "roles" => $user->getRoles()
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

        return new Response($serializer->serialize($response, "json"));
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
    public function getCurrentUserId()
    {
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

        return new Response($serializer->serialize($response, "json"));
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
     *                  @OA\Property(property="userDataID", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="surnames", type="string"),
     *                  @OA\Property(property="nif", type="string"),
     *                  @OA\Property(property="phone", type="string"),
     *                  @OA\Property(property="address", type="string"),
     *                  @OA\Property(property="postal_code", type="string"),
     *                  @OA\Property(property="city", type="string"),
     *                  @OA\Property(property="province", type="string"),
     *                  @OA\Property(property="country", type="string"),
     *                  @OA\Property(property="email", type="string"),
     *                  @OA\Property(property="last_login", type="datetime"),
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
    public function getUserData(ManagerRegistry $doctrine, $user_id=null)
    {
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
                        "name" => $userData->getName() ? $userData->getName() : '',
                        "surname" => $userData->getSurname() ? $userData->getSurname() : '',
                        "dni" => $userData->getDni() ? $userData->getDni() : '',
                        // "phone" => $userData->getPhoneNumber() ? $userData->getPhoneNumber() : '',
                        // "address" => $userData->getAddress() ? $userData->getAddress() : '',
                        // "postal_code" => $userData->getPostalCode() ? $userData->getPostalCode() : '',
                        // "city" => $userData->getCity() ? $userData->getCity() : '',
                        // "province" => $userData->getProvince() ? $userData->getProvince() : '',
                        // "country" => $userData->getCountry() ? $userData->getCountry() : '',
                        "email" => $userData->getEmail() ? $userData->getEmail() : $userData->getUser()->getEmail(),
                        // "last_login" => $userData->getLastLogin() ? $userData->getLastLogin() : ''

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

        return new Response($serializer->serialize($response, "json"));
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
    public function getUsersData(ManagerRegistry $doctrine, Request $request, $role = null)
    {
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
                                // "username" => $us->getUsername() ? $us->getUsername() : '',
                                "roles" => $us->getRoles() ? $us->getRoles() : '',
                                "name" => $us->getUserData()->getName() ? $us->getUserData()->getName() : '',
                                "surname" => $us->getUserData()->getSurname() ? $us->getUserData()->getSurname() : '',
                                // "phone_number" => $us->getUSerData()->getPhoneNumber() ? $us->getUSerData()->getPhoneNumber() : '',
                                // "address" => $us->getUSerData()->getAddress() ? $us->getUSerData()->getAddress() : '',
                                // "city" => $us->getUSerData()->getCity() ? $us->getUSerData()->getCity() : '',
                                // "postal_code" => $us->getUSerData()->getPostalCode() ? $us->getUSerData()->getPostalCode() : '',
                                // "province" => $us->getUSerData()->getProvince() ? $us->getUSerData()->getProvince() : '',
                                // "country" => $us->getUSerData()->getCountry() ? $us->getUSerData()->getCountry() : '',
                                "dni" => $us->getUserData()->getDni() ? $us->getUserData()->getDni() : '',
                                // "last_login" => $us->getUSerData()->getLastlogin() ? $us->getUSerData()->getLastlogin() : '',
                                "is_deleted" => $us->isDeleted() ? $us->isDeleted() : '',
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
                                // "username" => $us->getUsername() ? $us->getUsername() : '',
                                "roles" => $us->getRoles() ? $us->getRoles() : '',
                                "name" => $us->getUserData()->getName() ? $us->getUserData()->getName() : '',
                                // "name" => $uUSerData->getName(),
                                "surname" => $us->getUserData()->getSurname() ? $us->getUserData()->getSurname() : '',
                                // "surname" => $uUSerData->getSurname(),
                                // "phone_number" => $us->getUSerData()->getPhoneNumber() ? $us->getUSerData()->getPhoneNumber() : '',
                                //"phone_number" => $uUSerData->getPhoneNumber(),
                                // "address" => $us->getUSerData()->getAddress() ? $us->getUSerData()->getAddress() : '',
                                // "city" => $us->getUSerData()->getCity() ? $us->getUSerData()->getCity() : '',
                                // "postal_code" => $us->getUSerData()->getPostalCode() ? $us->getUSerData()->getPostalCode() : '',
                                // "province" => $us->getUSerData()->getProvince() ? $us->getUSerData()->getProvince() : '',
                                // "country" => $us->getUSerData()->getCountry() ? $us->getUSerData()->getCountry() : '',
                                "dni" => $us->getUserData()->getDni() ? $us->getUserData()->getDni() : '',
                                // "last_login" => $us->getUSerData()->getLastlogin() ? $us->getUSerData()->getLastlogin() : '',
                                "is_deleted" => $us->isDeleted() ? $us->isDeleted() : '',
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

        // return $this->dtoService->getJson($response);
        return new Response($serializer->serialize($response, "json"));

    }

    /**
     * @Route(
     *     "/user/delete/{id}",
     *     name="Delete user by id",
     *     methods={ "DELETE" },
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
    
    public function removeUser(ManagerRegistry $doctrine, $id)
    {
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

        return new Response($serializer->serialize($response, "json"));
        // return $this->dtoService->getJson($response);
    }

}
