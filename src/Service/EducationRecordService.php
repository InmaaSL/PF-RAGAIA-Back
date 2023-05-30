<?php

namespace App\Service;

use App\Repository\EducationRecordRepository;
use App\Entity\EducationRecord;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


class EducationRecordService {
    private $educationRecordsRepo;
    private $userRepo;

    /**
     * EducationRecordService constructor.
     * @param EducationRecordRepository $educationRecordsRepo
     * @param UserRepository $userRepo
     */
    public function __construct(EducationRecordRepository $educationRecordsRepo, UserRepository $userRepo) {
        $this->educationRecordsRepo = $educationRecordsRepo;
        $this->userRepo = $userRepo;
    }

    public function get($dataRequested) {
        return $this->educationRecordsRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->educationRecordsRepo->getById($id);
    }

    public function getAll() {
        return $this->educationRecordsRepo->getAllSortId();
    }

}