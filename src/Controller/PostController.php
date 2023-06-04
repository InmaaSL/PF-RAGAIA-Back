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
use App\Entity\Post;
use App\Entity\TopicPost;
use App\Entity\PostMessage;

use OpenApi\Annotations as OA;
use App\Service\PermissionService;
use App\Service\DtoService;
use App\Service\RestService;
use App\Service\PostService;

use DateTime;


/**
 * @Route("/api", name="api_")
 */
class PostController extends BaseControllerWithExtras
{
    private $postService;

    /**
     * MealsController constructor.
     * @param DtoService $dtoSvc
     * @param PostService $postService
     * 
     */
    public function __construct(
        DtoService $dtoSvc,
        RestService $restService,
        PostService $postService
        ) {
        parent::__construct($restService, $dtoSvc);
        $this->restService = $restService;
        $this->dtoService = $dtoSvc;
        $this->postService = $postService;
    }

    /**
     * @Route(
     *     "/getTopicPost",
     *     name="Get all the topics post",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all the topics post"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the topic post",
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
    public function getTopicPost(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $topicPost= $doctrine->getRepository(TopicPost::class)->findAll();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["topicPost:main"];
        return $this->dtoService->getJson($topicPost, $groups);
    }


    /**
     * @Route(
     *     "/savePost",
     *     name="Register post",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving post"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Post successfully saved",
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
     *              required={"topic", "title", "message" },
     *              @OA\Property(
     *                  property="topic",
     *                  description="Topic",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  description="Title",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  description="Message",
     *                  type="boolean"
     *              )
     *          )
     *      )
     * )
     * 
     */
    public function savePost(ManagerRegistry $doctrine, Request $request, Security $security){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $topic_id = $request->get('topic');
            $title = $request->get('title');
            $message = $request->get('message');
            $user = $security->getUser();

            date_default_timezone_set('Europe/Madrid');
            
            $topic = $doctrine->getRepository(TopicPost::class)->find($topic_id);
            
            $post = new Post();
            $post->setTopic($topic);
            $post->setTitle($title);
            $post->setMessage($message);
            $post->setUser($user);
            $post->setDate(new DateTime());

            $em->persist($post);
            $em->flush();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $post : $message,
        ];

        $groups = ['post:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/getAllPost",
     *     name="Get all post",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting all the topics post"
     * )
     *
     * @OA\Response(
     *     response="200",
     *     description="Get all the topic post",
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
    public function getAllPost(ManagerRegistry $doctrine){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $message = "";

        try {
            $code = 200;
            $error = false;

            $post= $doctrine->getRepository(Post::class)->findAll();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get my user info - Error: {$ex->getMessage()}";
        }

        $groups = ["post:main"];
        return $this->dtoService->getJson($post, $groups);
    }

    /**
     * @Route(
     *     "/getThisPost/{post_id}",
     *     name="Get this post",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting post"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Post successfully getting",
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
     *     name="post_id",
     *     in="path",
     *     required=true,
     *     description="Post ID",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function getThisPost(ManagerRegistry $doctrine, Request $request, $post_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $post = $doctrine->getRepository(Post::class)->find($post_id);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to delete the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $post : $message,
        ];

        $groups = ['post:main'];
        return $this->dtoService->getJson($post, $groups);
    }



    /**
     * @Route(
     *     "/v2/post/{id}",
     *     name="post",
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
    public function getAllPostPaginated(Request $request, $id) {
        $group = ["post:main"];

        $dataRequested = $this->restService->getRequestedData($request);
        $post = $this->postService->get($dataRequested);
        return $this->dtoService->getJson($post,$group);
    }

    /**
     * @Route(
     *     "/deletePost/{post_id}",
     *     name="Delete post",
     *     methods={ "DELETE" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error deleting post"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Post successfully deleting",
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
     *     name="post_id",
     *     in="path",
     *     required=true,
     *     description="Post ID",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function deletePost(ManagerRegistry $doctrine, Request $request, $post_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $post = $doctrine->getRepository(Post::class)->find($post_id);
        
            $em->remove($post);
            $em->flush();


        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to delete the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? 'Registro eliminado' : $message,
        ];

        $groups = ['post:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/savePostMessage/{post_id}",
     *     name="Register post message",
     *     methods={ "POST" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error saving post message"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Post message successfully saved",
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
     *              required={"message" },
     *              @OA\Property(
     *                  property="message",
     *                  description="Message",
     *                  type="boolean"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Parameter(
     *     name="post_id",
     *     in="path",
     *     required=true,
     *     description="Post ID",
     *     @OA\Schema(type="string")
     * )

     * 
     */
    public function savePostMessage(ManagerRegistry $doctrine, Request $request, Security $security, $post_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $message = $request->get('message');
            $user = $security->getUser();

            date_default_timezone_set('Europe/Madrid');
            
            $post = $doctrine->getRepository(Post::class)->find($post_id);
            
            $postMessage = new PostMessage();
            $postMessage->setPost($post);
            $postMessage->setMessage($message);
            $postMessage->setUser($user);
            $postMessage->setDate(new DateTime());

            $em->persist($postMessage);
            $em->flush();

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $postMessage : $message,
        ];

        $groups = ['postMessage:main'];
        return $this->dtoService->getJson($response, $groups);
    }

    /**
     * @Route(
     *     "/getPostMessages/{post_id}",
     *     name="Get this post message",
     *     methods={ "GET" },
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="Error getting post messages"
     * )
     * 
     * @OA\Response(
     *     response="200",
     *     description="Post messages successfully getting",
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
     *     name="post_id",
     *     in="path",
     *     required=true,
     *     description="Post ID",
     *     @OA\Schema(type="string")
     * )
     * 
     */
    public function getPostMessages(ManagerRegistry $doctrine, Request $request, $post_id){
        
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $em = $doctrine->getManager();

        $message = "";

        try {
            $code = 200;
            $error = false;
            
            $post = $doctrine->getRepository(PostMessage::class)->findBy(['post' => $post_id]);

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to delete the post - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $post : $message,
        ];

        $groups = ['postMessage:main'];
        return $this->dtoService->getJson($post, $groups);
    }


}
