<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Repository\HealthDocumentRepository;
use App\Entity\HealthDocument;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;


class HealthDocumentService {
    private $healthDocumentRepo;
    private $userRepo;

    /**
     * HealthDocumentService constructor.
     * @param HealthDocumentRepository $healthDocumentRepo
     * @param UserRepository $userRepo
     */
    public function __construct(HealthDocumentRepository $healthDocumentRepo, UserRepository $userRepo) {
        $this->healthDocumentRepo = $healthDocumentRepo;
        $this->userRepo = $userRepo;
    }

    public function get($dataRequested) {
        return $this->healthDocumentRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->healthDocumentRepo->getById($id);
    }

    public function getAll() {
        return $this->healthDocumentRepo->getAllSortId();
    }

}