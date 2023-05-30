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
use App\Entity\EducationDocument;
use App\Entity\EducationRecord;
use App\Repository\EducationDocumentRepository;
use App\Repository\EducationRecordRepository;

use App\Service\PermissionService;
use App\Service\DtoService;
use App\Service\RestService;
use App\Service\FileUploader;
use App\Service\EducationDocumentService;
use App\Service\EducationRecordService;

use DateTime;

/**
 * @Route("/api", name="api_")
 */
class EducationController extends BaseControllerWithExtras
{

    private $educationDocumentService;
    private $educationRecordService;

        /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     * @param EducationDocumentService $educationDocumentService
     * @param EducationRecordService $educationRecordService
     * 
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService,
        EducationDocumentService $educationDocumentService,
        EducationRecordService $educationRecordService
        ) {
        parent::__construct($restService, $dtoSvc, $educationDocumentService);
        $this->restService = $restService;
        $this->dtoService = $dtoSvc;
        $this->educationDocumentService = $educationDocumentService;
        $this->educationRecordService = $educationRecordService;
    }

    /**
     * @Route(
     *     "/setEducationDocument/{user_id}",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error user education document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User education document successfully setter",
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
    public function setEducationDocument(ManagerRegistry $doctrine, Request $request, string $uploadDir, FileUploader $uploader, $user_id)
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

                $up = $uploadDir . '/user'.$user_id.'/education';

                // $files = glob($up.'/*');
                // foreach ($files as $file){
                //     if(is_file($file)){
                //         unlink($file);
                //     }
                // }
                
                $uploader->uploadDocument($up, $document, $name);

                $educationDocument = new EducationDocument();
                $educationDocument->setUser($user);
                $educationDocument->setDate(new \DateTime());
                $educationDocument->setFile($name);
                $educationDocument->setNameFile($file_name);

                $em->persist($educationDocument);
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
            $message = "An error has occurred trying to register the user education document - Error: {$ex->getMessage()}";
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
     *     "/getEducationDocument/{document_id}",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting user education document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="User education document retrieved",
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
    public function getEducationDocument(ManagerRegistry $doctrine, Request $request, $document_id)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $em->getRepository(EducationDocument::class)->find($document_id);

            if($document instanceof EducationDocument)
            {
                $user_id = $document->getUser()->getId();
                $userDocument = $document->getFile();
                if($userDocument == null){
                    $url_document = null;
                }else{
                    $url_document = '/uploads/user'.$user_id.'/education/'.$userDocument;
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
            $message = "An error has occurred trying to get the user education document - Error: {$ex->getMessage()}";
        }

        $response = [
            'url' => $url_document,
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $document : $message,
        ];

        $group = ["educationDocument:main"];

        return $this->dtoService->getJson($response, $group);
    }

    /**
     * @Route(
     *     "/deleteEducationDocument/{document_id}",
     *     methods={ "DELETE" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user education document"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Education document deleted",
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
    public function deleteEducationDocument(ManagerRegistry $doctrine, Request $request, $document_id, $uploadDir)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $document = $em->getRepository(EducationDocument::class)->find($document_id);

            if($document instanceof EducationDocument)
            {

                $user_id = $document->getUser()->getId();
                $userDocument = $document->getFile();

                if($userDocument == null){
                    $url_document = null;
                }else{
                    $url_document = $uploadDir . '/user'.$user_id.'/education/'.$userDocument;
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
     *     "/v2/getAllUserEducationDocument/{id}",
     *     name="Get all education document paginated",
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
    public function getAllUserEducationDocumentPaginated(Request $request, $id) {
        $group = ["educationDocument:main"];
        
        $dataRequested = $this->restService->getRequestedData($request);
        $document = $this->educationDocumentService->get($dataRequested);
        return $this->dtoService->getJson($document,$group);
    }

    /**
     * @Route(
     *     "/setEducationRecord/{user_id}",
     *     name="Set a user education record",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving user education record"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User education record successfully saved",
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
     *              required={"type_record", "description"},
     *              @OA\Property(
     *                  property="type_record",
     *                  description="Type consultation",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  description="Description",
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

            $type_record = $request->get('type_record');
            $description = $request->get('description');
            // $consultation_date = $request->get('consultation_date');

            if($nna instanceof User)
            {
                $educationRecord = new EducationRecord();
                $educationRecord->setUser($nna);
                $educationRecord->setTypeRecord($type_record);
                $educationRecord->setDate(new \DateTime());
                // $educationRecord->setConsultationDate(new \DateTime($consultation_date));
                $educationRecord->setDescription($description);
                $educationRecord->setWorker($worker);
                $educationRecord->setIsDeleted(0);

    
                $em->persist($educationRecord);
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
            'data' => $code == 200 ? $educationRecord : $message,
        ];

        $groups = ["educationRecord:main"];
        return $this->dtoService->getJson($educationRecord, $groups);
    }

    /**
     * @Route(
     *     "/editEducationRecord/{education_record_id}",
     *     name="Edit a user education record",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving user education record"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User education record successfully edited",
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
     *              required={"type_record", "description"},
     *              @OA\Property(
     *                  property="type_record",
     *                  description="Type consultation",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  description="Description",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="education_record_id",
     *     in="path",
     *     required=true,
     *     description="Education record Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function editHealthRecord(ManagerRegistry $doctrine, Request $request, Security $security, $education_record_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $educationRecord = $doctrine->getRepository(EducationRecord::class)->find($education_record_id);
            $worker = $security->getUser();

            $type_record = $request->get('type_record');
            $description = $request->get('description');
            // $consultation_date = $request->get('consultation_date');

            if($educationRecord instanceof EducationRecord)
            {
                $educationRecord->setTypeRecord($type_record);
                $educationRecord->setDate(new \DateTime());
                // $educationRecord->setConsultationDate(new \DateTime($consultation_date));
                $educationRecord->setDescription($description);
                $educationRecord->setWorker($worker);
                $educationRecord->setIsDeleted(0);

                $em->persist($educationRecord);
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
            $message = "An error has occurred trying to register the user education record - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,            
            'error' => $error,
            'data' => $code == 200 ? $educationRecord : $message,
        ];

        $groups = ["educationRecord:main"];
        return $this->dtoService->getJson($educationRecord, $groups);
    }

    /**
     * @Route(
     *     "/deleteEducationRecord/{education_record_id}",
     *     name="Deleted a user education record",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting user education record"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="User education record successfully deleted",
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
     *     name="education_record_id",
     *     in="path",
     *     required=true,
     *     description="Education record Id",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function deletedEducationRecord(ManagerRegistry $doctrine, Request $request, $education_record_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;

            $educationRecord = $doctrine->getRepository(EducationRecord::class)->find($education_record_id);


            if($educationRecord instanceof EducationRecord)
            {
                $educationRecord->setIsDeleted(1);
                $em->persist($educationRecord);
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
            'data' => $code == 200 ? $educationRecord : $message,
        ];

        $groups = ["educationRecord:main"];
        return $this->dtoService->getJson($educationRecord, $groups);
    }

    /**
     * @Route(
     *     "/v2/educationRecord/{id}",
     *     name="Education Record",
     *     methods={ "GET" },
     * )
     * 
     * * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="Education record id",
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
    public function getEducationRecordPaginated(Request $request, $id) {
        $group = ["educationRecord:main"];
        
        $dataRequested = $this->restService->getRequestedData($request);
        $healthRecord = $this->educationRecordService->get($dataRequested);
        return $this->dtoService->getJson($healthRecord,$group);
    }




}
