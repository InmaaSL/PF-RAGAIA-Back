<?php

namespace App\Service;

// use App\Repository\UserRepository;
use App\Repository\PostRepository;
// use App\Entity\User;
use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;

// use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
// use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;


class PostService {
    private $postRepo;
    // private $userRepo;

    /**
     * PostRepository constructor.
     * @param PostRepository $postRepo
    //  * @param UserRepository $userRepo
     */
    public function __construct(PostRepository $postRepo) {
        $this->postRepo = $postRepo;
        // $this->userRepo = $u;
    }

    public function get($dataRequested) {
        return $this->postRepo->get($dataRequested);
    }

    public function getById($id) {
        return $this->postRepo->getById($id);
    }

    public function getAll() {
        return $this->postRepo->getAllSortId();
    }

}