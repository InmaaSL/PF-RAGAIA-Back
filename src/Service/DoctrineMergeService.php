<?php

    namespace App\Service;
    
    use Doctrine\Persistence\ManagerRegistry;
    
    class DoctrineMergeService
    {
        /**
         * @var \Doctrine\Persistence\ObjectManager
         */
        private $em;

        /**
         * @param \Doctrine\Persistence\ManagerRegistry $em
         */
        public function __construct(ManagerRegistry $mr)
        {
            $this->em = $mr->getManager();
        }

        /**
         * @param object $entity
         *
         * @return object
         *
         * @throws \Doctrine\ORM\ORMException
         * @throws \Doctrine\ORM\OptimisticLockException
         * @throws \Doctrine\ORM\TransactionRequiredException
         */
        public function merge(object $entity): object
        {
            $mergedEntity = null;
            $className = get_class($entity);
            $identifiers = $this->getIdentifiersFromEntity($entity);
            $entityFromDoctrine = $this->em->find($className, $identifiers);

            if ($entityFromDoctrine) {
                $mergedEntity = $this->mergeEntities($entityFromDoctrine, $entity);
            } else {
                $this->em->persist($entity);
                $mergedEntity = $entity;
            }

            return $mergedEntity;
        }

        /**
         * @param object $entity
         *
         * @return array
         */
        private function getIdentifiersFromEntity(object $entity): array
        {
            $className = get_class($entity);
            $meta = $this->em->getClassMetadata($className);
            $identifiers = $meta->getIdentifierValues($entity);

            return $identifiers;
        }

        /**
         * @param object $first
         * @param object $second
         *
         * @return object
         */
        private function mergeEntities(object $first, object $second): object
        {
            $classNameFirst = get_class($first);
            $metaFirst = $this->em->getClassMetadata($classNameFirst);
            $classNameSecond = get_class($second);
            $metaSecond = $this->em->getClassMetadata($classNameSecond);

            $fieldNames = $metaFirst->getFieldNames();
            foreach ($fieldNames as $fieldName) {
                $secondValue = $metaSecond->getFieldValue($second, $fieldName);
                $metaFirst->setFieldValue($first, $fieldName, $secondValue);
            }

            return $first;
        }
    }