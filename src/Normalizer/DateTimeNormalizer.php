<?php declare (strict_types=1);

    namespace App\Normalizer;

    use Symfony\Component\Serializer\Exception\ExceptionInterface;

    /**
     * Entity normalizer
     */
    class DateTimeNormalizer extends \Symfony\Component\Serializer\Normalizer\DateTimeNormalizer {

        /**
         * @param \DateTimeInterface $object
         * @param null $format
         * @param array $context
         * @return array|\ArrayObject|bool|float|int|string|null
         * @throws ExceptionInterface
         */
        public function normalize(mixed $object, ?string $format = null, array $context = []): String {
            if ($object == null || $object->getTimestamp() == false) return null;
            return parent::normalize($object, $format, $context);
        }
    }