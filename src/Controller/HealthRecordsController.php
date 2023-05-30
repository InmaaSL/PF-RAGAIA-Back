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

use App\Entity\User;
use App\Entity\HealthRecords;
use App\Entity\HealthDocument;
use App\Controller\Exception;
use App\Entity\UserProfessionalCategoryCentre;
use App\Repository\UserProfessionalCategoryCentreRepository;
use OpenApi\Annotations as OA;

use App\Service\PermissionService;
use App\Service\DtoService;
use App\Service\RestService;
use App\Service\HealthRecordService;
use App\Service\FileUploader;
use App\Service\HealthDocumentService;

use DateTime;

/**
 * @Route("/api", name="api_")
 */
class HealthRecordsController extends BaseControllerWithExtras
{

    // Crear registros médicos X
    // Editarlos X
    // Obtenerlos X
    // Eliminarlos. X

    private $healthDocumentService;
    private $healthRecordsService;

    /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     * @param HealthRecordService $healthRecordsService
     * 
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService,
        HealthRecordService $healthRecordsService,
        HealthDocumentService $healthDocumentService,
        ) {
        parent::__construct($restService, $dtoSvc, $healthRecordsService);
        $this->restService = $restService;
        $this->dtoService = $dtoSvc;
        $this->healthRecordsService = $healthRecordsService;
        $this->healthDocumentService = $healthDocumentService;
    }

    /**
     * @Route(
     *     "/setHealthRecord/{user_id}",
     *     name="Set a user heath record",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving user health record"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User hearth record successfully saved",
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
     *              required={"type_consultation", "what_happens", "diagnostic", "treatment"},
     *              @OA\Property(
     *                  property="type_consultation",
     *                  description="Type consultation",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="what_happens",
     *                  description="What happens",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="diagnostic",
     *                  description="Diagnostic",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="treatment",
     *                  description="Treatment",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="revision",
     *                  description="Revision",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="consultation_date",
     *                  description="Consultation Date",
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
    public function setHealthRecord(ManagerRegistry $doctrine, Request $request, $user_id, Security $security){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $repositoryUser = $doctrine->getRepository(User::class);
        
            $nna = $repositoryUser->find($user_id);
            $worker = $security->getUser();

            $type_consultation = $request->get('type_consultation');
            $what_happens = $request->get('what_happens');
            $diagnostic = $request->get('diagnostic');
            $treatment = $request->get('treatment');
            $revision = $request->get('revision');
            $consultation_date = $request->get('consultation_date');

            if($nna instanceof User)
            {
                $healthRecord = new HealthRecords();
                $healthRecord->setUser($nna);
                $healthRecord->setTypeConsultation($type_consultation);
                $healthRecord->setDate(new \DateTime());
                $healthRecord->setConsultationDate(new \DateTime($consultation_date));
                $healthRecord->setWhatHappens($what_happens);
                $healthRecord->setDiagnostic($diagnostic);
                $healthRecord->setTreatment($treatment);
                $healthRecord->setRevision($revision);
                $healthRecord->setWorker($worker);
                $healthRecord->setIsDeleted(0);

    
                $em->persist($healthRecord);
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
            $message = "An error has occurred trying to register the user hearth record - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $healthRecord : $message,
        ];

        $groups = ["healthRecord:main"];
        return $this->dtoService->getJson($healthRecord, $groups);
    }

    /**
     * @Route(
     *     "/editHealthRecord/{health_record_id}",
     *     name="Edit a user heath record",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving user health record"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User hearth record successfully edited",
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
     *              required={"type_consultation", "what_happens", "diagnostic", "treatment"},
     *              @OA\Property(
     *                  property="type_consultation",
     *                  description="Type consultation",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="what_happens",
     *                  description="What happens",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="diagnostic",
     *                  description="Diagnostic",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="treatment",
     *                  description="Treatment",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="revision",
     *                  description="Revision",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="consultation_date",
     *                  description="Consultation Date",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="health_record_id",
     *     in="path",
     *     required=true,
     *     description="Health record Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function editHealthRecord(ManagerRegistry $doctrine, Request $request, Security $security, $health_record_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $healthRecord = $doctrine->getRepository(HealthRecords::class)->find($health_record_id);
            $worker = $security->getUser();

            $type_consultation = $request->get('type_consultation');
            $what_happens = $request->get('what_happens');
            $diagnostic = $request->get('diagnostic');
            $treatment = $request->get('treatment');
            $revision = $request->get('revision');
            $consultation_date = $request->get('consultation_date');

            if($healthRecord instanceof HealthRecords)
            {
                $healthRecord->setTypeConsultation($type_consultation);
                $healthRecord->setDate(new \DateTime());
                $healthRecord->setConsultationDate(new \DateTime($consultation_date));
                $healthRecord->setWhatHappens($what_happens);
                $healthRecord->setDiagnostic($diagnostic);
                $healthRecord->setTreatment($treatment);
                $healthRecord->setRevision($revision);
                $healthRecord->setWorker($worker);
                $healthRecord->setIsDeleted(0);
    
                $em->persist($healthRecord);
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
            $message = "An error has occurred trying to register the user hearth record - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,            
            'error' => $error,
            'data' => $code == 200 ? $healthRecord : $message,
        ];

        $groups = ["healthRecord:main"];
        return $this->dtoService->getJson($healthRecord, $groups);
    }

    /**
     * @Route(
     *     "/deleteHealthRecord/{health_record_id}",
     *     name="Deleted a user heath record",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user health record"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User hearth record successfully deleted",
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
     *              required={"is_deleted"},
     *              @OA\Property(
     *                  property="is_deleted",
     *                  description="Is deleted",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="health_record_id",
     *     in="path",
     *     required=true,
     *     description="Health record Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function deletedHealthRecord(ManagerRegistry $doctrine, Request $request, $health_record_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $healthRecord = $doctrine->getRepository(HealthRecords::class)->find($health_record_id);

            $is_deleted = $request->get('is_deleted');

            if($healthRecord instanceof HealthRecords)
            {
                $healthRecord->setIsDeleted($is_deleted);
                $em->persist($healthRecord);
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
            $message = "An error has occurred trying to delete the user hearth record - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $healthRecord : $message,
        ];

        $groups = ["healthRecord:main"];
        return $this->dtoService->getJson($healthRecord, $groups);
    }

    /**
     * @Route(
     *     "/v2/healthRecord/{id}",
     *     name="healthRecord",
     *     methods={ "GET" },
     * )
     * 
     * * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="healthRecord id",
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
    public function getHealthRecordPaginated(Request $request, $id) {
        $group = ["healthRecord:main"];
        
        $dataRequested = $this->restService->getRequestedData($request);
        $healthRecord = $this->healthRecordsService->get($dataRequested);
        return $this->dtoService->getJson($healthRecord,$group);
    }

    /**
     * @Route(
     *     "/setHealthDocument/{user_id}",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error user health document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User health document successfully setter",
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
    public function setHealthDocument(ManagerRegistry $doctrine, Request $request, string $uploadDir, FileUploader $uploader, $user_id)
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
                $name = md5(uniqid());
                $up = $uploadDir;

                if(!is_dir($up))
                {
                    mkdir($up, 0755, true);
                }

                $up = $uploadDir . '/user'.$user_id.'/health';

                // $files = glob($up.'/*');
                // foreach ($files as $file){
                //     if(is_file($file)){
                //         unlink($file);
                //     }
                // }
                
                $uploader->uploadDocument($up, $document, $name);

                $healthDocument = new HealthDocument();
                $healthDocument->setUser($user);
                $healthDocument->setDate(new \DateTime());
                $healthDocument->setFile($name);
                $healthDocument->setNameFile($file_name);

                $em->persist($healthDocument);
                $em->flush();
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
     *     "/getHealthDocument/{document_id}",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting user health document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User health document retrieved",
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
    public function getHealthDocument(ManagerRegistry $doctrine, Request $request, $document_id)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $em->getRepository(HealthDocument::class)->find($document_id);

            if($document instanceof HealthDocument)
            {
                $user_id = $document->getUser()->getId();
                $userDocument = $document->getFile();
                if($userDocument == null){
                    $url_document = null;
                }else{
                    $url_document = '/uploads/user'.$user_id.'/health/'.$userDocument;
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

        $group = ["healthDocument:main"];

        return $this->dtoService->getJson($response, $group);
    }

    /**
     * @Route(
     *     "/deleteHealthDocument/{document_id}",
     *     methods={ "DELETE" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user health document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Health document deleted",
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
    public function deleteHealthDocument(ManagerRegistry $doctrine, Request $request, $document_id, $uploadDir)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $em->getRepository(HealthDocument::class)->find($document_id);

            if($document instanceof HealthDocument)
            {

                $user_id = $document->getUser()->getId();
                $userDocument = $document->getFile();

                if($userDocument == null){
                    $url_document = null;
                }else{
                    $url_document = $uploadDir . '/user'.$user_id.'/health/'.$userDocument;
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
     *     "/v2/getAllUserHealthDocument/{id}",
     *     name="Get all health document paginated",
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
    public function getAllUserHealthDocumentPaginated(Request $request, $id) {
        $group = ["healthDocument:main"];
        
        $dataRequested = $this->restService->getRequestedData($request);
        $document = $this->healthDocumentService->get($dataRequested);
        return $this->dtoService->getJson($document,$group);
    }



}
