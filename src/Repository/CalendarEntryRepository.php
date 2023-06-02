<?php

namespace App\Repository;

use App\Entity\CalendarEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends ServiceEntityRepository<CalendarEntry>
 *
 * @method CalendarEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarEntry[]    findAll()
 * @method CalendarEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarEntryRepository extends ServiceEntityRepositoryCustom
{
    public function __construct(ManagerRegistry $registry, ValidatorInterface $validator)
    {
        parent::__construct($registry, $validator, CalendarEntry::class);
    }

    public function add(CalendarEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CalendarEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CalendarEntry[] Returns an array of CalendarEntry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CalendarEntry
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
