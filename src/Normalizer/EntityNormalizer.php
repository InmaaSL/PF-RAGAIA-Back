<?php declare (strict_types=1);

    namespace App\Normalizer;

    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

    /**
     * Entity normalizer
     */
    class EntityNormalizer implements DenormalizerInterface {
        /** @var EntityManagerInterface * */
        protected $em;

        public function __construct(EntityManagerInterface $em) {
            $this->em = $em;
        }

        /**
         * @inheritDoc
         */
        public function supportsDenormalization($data, $type, $format = null, $context = null) {
            return $data == null || (strpos($type, 'App\\Entity\\') === 0 && ($this->getId($data)));
        }

        /**
         * @inheritDoc
         */
        public function denormalize($data, $class, $format = null, array $context = []) {
            if ($data == null) return null;
            else return $this->em->find($class, $data);
        }

        private function getId($data) {
            $id = null;
            if (is_numeric($data)) $id = $data;
            return $id;
        }
    }