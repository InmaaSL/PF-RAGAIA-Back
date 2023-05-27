<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\ExpedientRepository;
use App\Entity\User;
use App\Entity\Expedient;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;


class ExpedientService {
    private $expedientRepo;
    private $userRepo;

    /**
     * ExpedientService constructor.
     * @param ExpedientRepository $expedientRepo
     * @param UserRepository $userRepo
     */
    public function __construct(ExpedientRepository $expedientRepo, UserRepository $userRepo) {
        $this->expedientRepo = $expedientRepo;
        $this->userRepo = $userRepo;
    }

    public function get($dataRequested) {
        return $this->expedientRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->expedientRepo->getById($id);
    }

    public function getAll() {
        return $this->expedientRepo->getAllSortId();
    }

}