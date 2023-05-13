<?php

namespace App\Repository;

use App\Entity\UserProfessionalCategoryCentre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;
/**
 * @extends ServiceEntityRepository<UserProfessionalCategoryCentre>
 *
 * @method UserProfessionalCategoryCentre|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProfessionalCategoryCentre|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProfessionalCategoryCentre[]    findAll()
 * @method UserProfessionalCategoryCentre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProfessionalCategoryCentreRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProfessionalCategoryCentre::class);
    }

    public function save(UserProfessionalCategoryCentre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserProfessionalCategoryCentre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserCentreAndCategory($userId, $centreId, $categoryId)
    {
        $qb = $this->createQueryBuilder('upcc')
            ->select('upcc')
            ->join('upcc.user', 'u')
            ->join('upcc.centre', 'c')
            ->join('upcc.professionalCategory', 'pc')
            ->where('u.id = :userId')
            ->andWhere('c.id = :centreId')
            ->andWhere('pc.id = :categoryId')
            ->setParameter('userId', $userId)
            ->setParameter('centreId', $centreId)
            ->setParameter('categoryId', $categoryId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getProfessionalCategoriesByUserAndCenter($userId, $centreId)
    {
        $qb = $this->createQueryBuilder('upcc');
        $qb->select('upcc.id')
            ->join('upcc.professionalCategory', 'pc')
            ->where('upcc.user = :userId')
            ->andWhere('upcc.centre = :centreId')
            ->setParameters([
                'userId' => $userId,
                'centreId' => $centreId
            ]);

        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return UserProfessionalCategoryCentre[] Returns an array of UserProfessionalCategoryCentre objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserProfessionalCategoryCentre
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


}
