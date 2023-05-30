<?php

    namespace App\Service;


    use App\Entity\User;
    use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
    use Symfony\Component\Security\Core\Security;

    class PermissionService {

        private $security;

        /**
         * UserService constructor.
         */
        public function __construct(Security $security) {
            $this->security = $security;
        }

        public function getUser(): User {
            /** @var User $user */
            $user = $this->security->getUser();
            return $user;
        }

        public function getUserId(): int {
            return $this->getUser()->getId();
        }
    }