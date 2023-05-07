<?php

namespace App\Repository;

use App\Entity\ProfesionalCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProfesionalCategory>
 *
 * @method ProfesionalCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfesionalCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfesionalCategory[]    findAll()
 * @method ProfesionalCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesionalCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfesionalCategory::class);
    }

    public function save(ProfesionalCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProfesionalCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ProfesionalCategory[] Returns an array of ProfesionalCategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProfesionalCategory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
