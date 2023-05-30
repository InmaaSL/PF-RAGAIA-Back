<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Repository\EducationDocumentRepository;
use App\Entity\EducationDocument;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;


class EducationDocumentService {
    private $educationDocumentRepo;
    private $userRepo;

    /**
     * EducationDocumentService constructor.
     * @param EducationDocumentRepository $educationDocumentRepo
     * @param UserRepository $userRepo
     */
    public function __construct(EducationDocumentRepository $educationDocumentRepo, UserRepository $userRepo) {
        $this->educationDocumentRepo = $educationDocumentRepo;
        $this->userRepo = $userRepo;
    }

    public function get($dataRequested) {
        return $this->educationDocumentRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->educationDocumentRepo->getById($id);
    }

    public function getAll() {
        return $this->educationDocumentRepo->getAllSortId();
    }

}