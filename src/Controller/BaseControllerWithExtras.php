<?php


    namespace App\Controller;


    use App\Entity\User;
    use App\Service\DtoService;
    use App\Service\PermissionService;
    use App\Service\RestService;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    // use Symfony\Component\Security\Core\Security;

    class BaseControllerWithExtras extends AbstractController {
        // protected $permissionSvc;
        protected $restService;
        protected $dtoService;

        public function __construct(RestService $restSvc, DtoService $dtoService) {
            $this->dtoService = $dtoService;
            // $this->permissionSvc = $permissionSvc;
            $this->restService = $restSvc;
        }

        // public function gateSuperAdmin(Security $security) {
        //     if (!$this->permissionSvc->isSuperAdmin($security)) $this->noNoNo();
        // }

        // public function gateAdmin(Security $security) {
        //     if (!$this->permissionSvc->isAdmin($security)) $this->noNoNo();
        // }        

        // public function gateWorker(Security $security) {
        //     if (!$this->permissionSvc->isWorker($security)) $this->noNoNo();
        // }

        // public function gateOwner(Security $security) {
        //     if (!$this->permissionSvc->isOwner($security)) $this->noNoNo();
        // }

        // public function gateServerRequest(Request $request) {
        //     $ip = $request->getClientIp();
        //     if (!($ip == '127.0.0.1')) $this->noNoNo();
        // }
        // public function gateSameUser(Security $security,User $user) {
        //     if ($this->permissionSvc->getUser($security)->getId() !== $user->getId()) $this->noNoNo();
        // }

        // private function noNoNo(){
        //     throw new AccessDeniedHttpException('Forbidden');
        // }

    }