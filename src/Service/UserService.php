<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\UserDataRepository;
use App\Entity\User;
use App\Entity\UserData;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;


class UserService {
    private $userRepo;
    private $userDataRepo;

    /**
     * UserService constructor.
     * @param UserRepository $userRepo
     * @param UserDataRepository $userDataRepo
     */
    public function __construct(UserRepository $userRepo, UserDataRepository $userDataRepo) {
        $this->userRepo = $userRepo;
        $this->userDataRepo = $userDataRepo;
    }

    public function get($dataRequested) {
        return $this->userRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->userRepo->getById($id);
    }

    public function getAll() {
        return $this->userRepo->getAllSortId();
    }

    public function getAllWorkers() {
        return $this->userRepo->getAllByRole("ROLE_WORKER");
    }

    public function getAllNNA($dataRequested) {
        return $this->userRepo->getAllByRole("ROLE_NNA");
    }

}