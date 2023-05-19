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
use App\Entity\Custody;
use App\Entity\ProfessionalCategory;
use App\Entity\UserProfessionalCategoryCentre;
use OpenApi\Annotations as OA;

use App\Service\DtoService;
use App\Service\RestService;

use DateTime;

use App\Repository\UserProfessionalCategoryCentreRepository;
/**
 * @Route("/api", name="api_")
 */
class RegistrationController extends BaseControllerWithExtras
{

    private $upccRepository;

    /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService,
        UserProfessionalCategoryCentreRepository $upccRepository
        // PermissionService $permissionSvc,
        ) {
        parent::__construct($restService, $dtoSvc);
        $this->restService = $restService;
        $this->dtoService = $dtoSvc;
        $this->upccRepository = $upccRepository;
    }

    /**
     * @Route("/register", 
     *      name="register", 
     *      methods={"POST"}    
     * )
     * 
     * @OA\Response(
     *     response=201,
     *     description="User was successfully registered"
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="User was not successfully registered"
     * )
     *
     * @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="application/x-www-form-urlencoded",
     *          @OA\Schema(
     *              type="object",
     *              required={"email", "password"},
     *              @OA\Property(
     *                  property="email",
     *                  description="The user email",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="The user password",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     *
     */
    public function register(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response{
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $email = $request->get('email');
        $password = $request->get('password');

        // $datos = $request->getContent();
        // $parameters = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword(
            $user,
            $password
        ));

        // $user->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
        $user->setConfirmed(false);
        $user->setDeleted(false);

        $userData = new UserData();
        $userData->setName('');
        $userData->setSurname('');
        $userData->setDni('');
        $userData->setEmail($email);

        $user->setUserData($userData);
        
        $em->persist($user);
        $em->persist($userData);
        $em->flush();

        $response = ($user);

        $groups = ["user:main"];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/user/userData/{user_id}",
     *     name="Register UserData",
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
     *              required={"dni", "name", "surnames", "email"},
     *              @OA\Property(
     *                  property="email",
     *                  description="User email",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="User name",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="surname",
     *                  description="User surname",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="dni",
     *                  description="User DNI/NIE",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  description="User phone",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="address",
     *                  description="User address",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="town",
     *                  description="town",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="province",
     *                  description="User province",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="postal_code",
     *                  description="User postal code",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="birth_date",
     *                  description="Date of Birth",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="admission_date",
     *                  description="Date of Admission",
     *                  type="date"
     *              ),
     *              @OA\Property(
     *                  property="custody_id",
     *                  description="Custody id",
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
    public function registerUserData(ManagerRegistry $doctrine, Request $request, $user_id){
        
        $repositoryUser = $doctrine->getRepository(User::class);
        $user = $repositoryUser->find($user_id);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();
        $repositoryUserData = $doctrine->getRepository(UserData::class);

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $name = $request->get('name');
            $surname = $request->get('surname');
            $email = $request->get('email');
            $dni = $request->get('dni');
            $phone = $request->get('phone');
            $address = $request->get('address');
            $town = $request->get('town');
            $province = $request->get('province');
            $postal_code = $request->get('postal_code');
            $birth_date = $request->get('birth_date');
            $admission_date = $request->get('admission_date');
            $custody_id = $request->get('custody_id');

            if($custody_id){
                $custody = $doctrine->getRepository(Custody::class)->find($custody_id);
            } else {
                $custody = null;
            }

            $user = $repositoryUser->find($user_id);

            if($user instanceof User)
            {
                $userData = $user->getUserData();
                $newUser = false;

                if(!$userData){
                    $newUser = true;
                    $userData = new UserData();
                }
                
                if(!$newUser){
                    // Comprobamos si algun USER tienes ese email en concreto:
                    $userExists = $repositoryUser->findOneBy(["email" => $email]);
                    
                    if(!$userExists || $userExists->getId() == $user->getId()){
                        $user->setEmail($email);
                        $userData->setName($name);
                        $userData->setSurname($surname);
                        $userData->setEmail($email);
                        $userData->setDni($dni);
                        $userData->setPhone($phone ? $phone : '');
                        $userData->setAddress($address ? $address : '');
                        $userData->setTown($town ? $town : '');
                        $userData->setProvince($province ? $province : '');
                        $userData->setPostalCode($postal_code ? $postal_code : '');
                        $userData->setBirthDate($birth_date ? new DateTime($birth_date) : null);
                        $userData->setAdmissionDate($admission_date ? new DateTime($admission_date) : null);
                        $userData->setCustody($custody);

                        $code = 200;
                        $error = false;
                        $message = "UserDate update";
                    } else {
                        $code = 500;
                        $error = true;
                        $message = "Email already exist.";   
                    }
                } else {
                    $userDataExists = $repositoryUserData->findOneBy(["email" => $email]);
                    if(!$userDataExists){
                        $userData->setUser($user);
                        $userData->setName($name);
                        $userData->setSurname($surname);
                        $userData->setDni($dni);
                        $userData->setEmail($email);
                        $userData->setPhone($phone ? $phone : '');
                        $userData->setAddress($address ? $address : '');
                        $userData->setTown($town ? $town : '');
                        $userData->setProvince($province ? $province : '');
                        $userData->setPostalCode($postal_code ? $postal_code : '');
                        $userData->setBirthDate($birth_date ? new DateTime($birth_date) : '');
                        $userData->setAdmissionDate($admission_date ? new DateTime($admission_date) : '');
                        $userData->setCustody($custody ? $custody : '');
                    } else {
                        $code = 500;
                        $error = true;
                        $message = "Email already exist in other userData.";   
                    }
                }

                $em->persist($user);
                $em->persist($userData);
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
            'data' => $code == 200 ? $user : $message,
        ];

        $groups = ["user:main"];
        return $this->dtoService->getJson($response, $groups);
    }

}
