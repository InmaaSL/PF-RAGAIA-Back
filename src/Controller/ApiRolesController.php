<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Security\Core\Security as SymfonySecurity;
use OpenApi\Annotations as OA;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Symfony\Component\Config\Definition\Exception\Exception;

use App\Entity\User;
use App\Entity\UserData;
use App\Entity\Company;

/**
 * @Route("/api", name="api_")
 */
class ApiRolesController extends BaseControllerWithExtras
{
    /**
     * @Route("/user/roles/{id}", name="user_roles", methods={"GET"})
     * @OA\Get(
     *      path="/api/user/roles/{id}",
     *      tags={"User"},
     *      summary="Get user roles",
     *      description="Get user roles",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User id",
     *          required=true,
     *          @OA\Schema(
     *          type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User roles",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function userRoles($id, ManagerRegistry $doctrine)
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $roles = $user->getRoles();
        return $this->json($roles);
    }

    //set user roles
    /**
     * @Route("/user/roles/{id}", name="user_roles_set", methods={"POST"})
     * @OA\Post(
     *      path="/api/user/roles/{id}",
     *      tags={"User"},
     *      summary="Set user roles",
     *      description="Set user roles",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"roles"},
     *                  @OA\Property(
     *                      property="roles",
     *                      description="Roles to set",
     *                      type="array",
    *                           @OA\Items(
    *                           type="string"
    *                       )
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User roles set",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function userRolesSet($id, Request $request, ManagerRegistry $doctrine)
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $roles = $request->request->get('roles');
        $roles = explode(",", $roles);
        $user->setRoles($roles);
        $doctrine->getManager()->flush();
        return $this->json($roles);
    }

    /**
     * @Route("/user/roles/{id}", name="user_roles_add", methods={"PUT"})
     * @OA\Put(
     *      path="/api/user/roles/{id}",
     *      tags={"User"},
     *      summary="Add user roles",
     *      description="Add user roles",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"roles"},
     *                  @OA\Property(
     *                      property="roles",
     *                      description="Roles to add",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string"
     *                      )
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User roles added",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * @Security(name="Bearer")
     */
    public function userRolesAdd($id, Request $request, ManagerRegistry $doctrine)
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $roles = $request->request->get('roles');
        $roles = explode(",", $roles);
        $user->addRoles($roles);
        $doctrine->getManager()->flush();
        return $this->json($user->getRoles());
    }

    /**
     * @Route("/user/roles/{id}", name="user_roles_remove", methods={"DELETE"})
     * @OA\Delete(
     *      path="/api/user/roles/{id}",
     *      tags={"User"},
     *      summary="Remove user roles",
     *      description="Remove user roles",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="User id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"roles"},
     *                  @OA\Property(
     *                      property="roles",
     *                      description="Roles to remove",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string"
     *                      )
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User roles removed",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * @Security(name="Bearer")
     */
    public function userRolesRemove($id, Request $request, ManagerRegistry $doctrine)
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        $roles = $request->request->get('roles');
        $roles = explode(",", $roles);
        $user->removeRoles($roles);
        $doctrine->getManager()->flush();
        return $this->json($user->getRoles());
    }
}
