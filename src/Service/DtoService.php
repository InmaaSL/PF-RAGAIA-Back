<?php

    namespace App\Service;


    use App\Normalizer\DateTimeNormalizer;
    use App\Normalizer\EntityNormalizer;
    use Doctrine\Common\Annotations\AnnotationReader;
    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
    use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
    use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
    use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
    use Symfony\Component\Serializer\Serializer;

    class DtoService {
        protected $serializer;

        /**
         * DtoService constructor.
         * @param EntityManager $em
         */
        public function __construct(EntityManagerInterface $em) {
            $classMetaDataFactory = new ClassMetadataFactory(
                new AnnotationLoader(
                    new AnnotationReader()
                )
            );
            $objectNormalizer = new ObjectNormalizer($classMetaDataFactory, null, null, new PhpDocExtractor());
            $normalizers = [new EntityNormalizer($em), new ArrayDenormalizer(), new DateTimeNormalizer(), $objectNormalizer];
            $encoders = [new JsonEncoder()];
            $this->serializer = new Serializer($normalizers, $encoders);

        }

        public function des($data, $class) {
            return $this->serializer->deserialize($data, $class, "json");
        }

        public function ser($data, $group = []) {
            return $this->serializer->serialize($data, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
                ObjectNormalizer::GROUPS => $group
            ]);
        }


        public function getJson($data, $group = []): Response {
            // Serialize your object in Json
            $jsonObject = $this->serializer->serialize($data, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
                ObjectNormalizer::GROUPS => $group
            ]);
            return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);
        }

        public function ok($content = 'true'): Response {
            return new Response($content, 200, ['Content-Type' => 'application/json']);
        }

        public function badParams($params = ''): Response {
            return new Response('Bad parameters for this request:
            
            ' . $params, 400, ['Content-Type' => 'application/json']);
        }


    }