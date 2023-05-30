<?php

namespace App\Service;

use App\Repository\HealthRecordsRepository;
use App\Entity\HealthRecords;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


class HealthRecordService {
    private $healthRecordsRepo;
    private $userRepo;

    /**
     * HealthRecordsService constructor.
     * @param HealthRecordsRepository $healthRecordsRepo
     * @param UserRepository $userRepo
     */
    public function __construct(HealthRecordsRepository $healthRecordsRepo, UserRepository $userRepo) {
        $this->healthRecordsRepo = $healthRecordsRepo;
        $this->userRepo = $userRepo;
    }

    public function get($dataRequested) {
        return $this->healthRecordsRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->healthRecordsRepo->getById($id);
    }

    public function getAll() {
        return $this->healthRecordsRepo->getAllSortId();
    }

}