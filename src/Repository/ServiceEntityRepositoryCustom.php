<?php

    namespace App\Repository;

    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
    use Doctrine\Persistence\ManagerRegistry;
    use Doctrine\DBAL\Exception as DBALException;
    use Doctrine\ORM\EntityRepository;
    use Doctrine\ORM\Tools\Pagination\Paginator;
    use LogicException;
    use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    
    use App\Service\DoctrineMergeService;


    class ServiceEntityRepositoryCustom extends EntityRepository implements ServiceEntityRepositoryInterface {
        private $validator;
        
        protected $doctrineMergeService;

        /**
         * @param ManagerRegistry $registry
         * @param ValidatorInterface $validator
         * @param string $entityClass The class name of the entity this repository manages
         */
        public function __construct(ManagerRegistry $registry, ValidatorInterface $validator, string $entityClass = "") {
            $this->validator = $validator;
            $manager = $registry->getManagerForClass($entityClass);
            if ($manager === null) {
                throw new LogicException(sprintf('EM missing for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.', $entityClass));
            }
            
            $this->doctrineMergeService = new DoctrineMergeService($registry, $entityClass);

            parent::__construct($manager, $manager->getClassMetadata($entityClass));
        }

        /**
         * Finds all entities in the repository, with the option to paginate and sort
         *
         * @param $paginate
         * @param $sort
         * @param $filter
         * @return array The entities
         */
        public
        function findAllPag($paginate, $sort, $filter) {
            $entityName = parent::getEntityName();
            $sortQ = $sort ? $this->buildOrder_Where("order", $sort) : "";
            $filterQ = $filter ? $this->buildOrder_Where("where", $filter) : "";

            $dql = parent::getEntityManager()->createQuery("SELECT ent FROM " . $entityName . " ent " . $filterQ . $sortQ)
                ->setFirstResult($paginate["page"] * $paginate["count"])->setMaxResults($paginate["count"]);
            $paginator = new Paginator($dql, true);
            try {
                return ["count" => count($paginator), "data" => $dql->getResult(), "dql" => $dql->getDQL()];
            } catch (\Doctrine\ORM\Query\QueryException $e) {
                throw new BadRequestHttpException($dql->getDQL());
            }
        }

        

        /**
         * Finds all entities in the repository, with the option to paginate and sort. mod
         *
         * @param $paginate
         * @param $sort
         * @param $filter
         * @return array The entities
         */
        public
        function findAllPagWithPrejoins($paginate, $sort, $filter, $preJoins = "") {
            $entityName = parent::getEntityName();
            $sortQ = $sort ? $this->buildOrder_Where("order", $sort) : "";
            $filterQ = $filter ? $this->buildOrder_Where("where", $filter, $preJoins) : $preJoins;

            $dql = parent::getEntityManager()->createQuery("SELECT ent FROM " . $entityName . " ent " . $filterQ . $sortQ)
                ->setFirstResult($paginate["page"] * $paginate["count"])->setMaxResults($paginate["count"]);
            $paginator = new Paginator($dql, true);
            try {
                return ["count" => count($paginator), "data" => $dql->getResult(), "dql" => $dql->getDQL()];
            } catch (\Doctrine\ORM\Query\QueryException $e) {
                throw new BadRequestHttpException($dql->getDQL());
            }
        }


        /**
         * @param $act | where or order
         * @param $inst | the associative array with the instructions to build this conditions or delimiters
         * @return string
         */
        public
        function buildOrder_Where($act, $inst, $preJoins = "") {
            $joins = $preJoins;
            if ($act == "where") {
                $out = " WHERE ";
                $sep = "AND ";
                $cond = function ($s) use (&$joins) {
                    if ((!is_null($s["value"]) && !empty($s["value"])) && ($s["mode"] == "entity" || $s["mode"] == "entityFieldExact" || $s["mode"] == "entityFieldLike") && strpos($joins, "INNER JOIN ent." . strtok($s["field"],".")) === false) {
                        $joins .= "INNER JOIN ent." . strtok($s["field"],".") . " " . strtok($s["field"],".") . " ";
                    }
                    if ((!is_null($s["value"]) && !empty($s["value"])) && ($s["mode"] == "reverseEntity" || $s["mode"] == "reverseEntityFieldExact" || $s["mode"] == "reverseEntityFieldLike") && strpos($joins, "INNER JOIN " . explode(":", $s["field"])[2]) === false) {
                        $joins .= "INNER JOIN " . explode(":", $s["field"])[2] . " " . strtok($s["field"],".") . " WITH ent = " . strtok($s["field"],".") . "." . explode(":", $s["field"])[1] . " ";
                    }
                    return !is_null($s["value"]) ? $this->buildCondition($s["mode"], $s["value"], $s["field"]) : false;
                };
            } else {//order
                $out = " ORDER BY";
                $sep = ", ";
                $cond = function ($s) {
                    if(strpos($s["prop"], ".") === false)
                        return (" ent." . $s["prop"] . " " . $s["dir"]);
                    return (" " . $s["prop"] . " " . $s["dir"]);
                };
            }
            $first = true;
            foreach ($inst as $k => $s) {
                $clause = $cond($s);
                if (!$clause) continue;
                $out .= $first ? $clause : (" " . $sep . $clause);
                $first = false;
            }
            return $joins . $out;
        }

        private
        function toStg($value) {
            if (is_bool($value)) return $value ? "true" : "false";
            else return $value;
        }

        public
        function caseInsensitiveLike(string $field, $value) {
            return " LOWER(" . $field . ") LIKE '%" . strtolower($value) . "%'";
        }

        public
        function toSQLStr($value) {
            return " '" . $value . "'";
        }

        /**
         * @param $mode | if it's like build a where clause LIKE '%String%' else assume related entity so look for it's id
         * @param $value | the val to compare to
         * @param $field | the field on the entity column or relation
         * @return string
         */
        private
        function buildCondition($mode, $value, $field) {
            $out = "";
            $buildClauses = function ($value) use ($field, $mode) {
                $field = explode(':',$field)[0];
                if(strpos($field, "+") !== false){
                    $fields = explode("+", $field);
                    $field = "CONCAT(";
                    foreach ($fields as $f){
                        $field .= $f . ", ' ', ";
                    }
                    $field = substr($field, 0, -7) . ")";
                }
                switch ($mode) {
                    case "like":
                        return $this->caseInsensitiveLike("ent." . $field, $value);
                    case "exact":
                        return "ent." . $field . " = " . $this->toStg($value);
                    case "entity":
                    case "reverseEntity":
                        return $field . ".id = " . $value;
                    case "entityFieldLike":
                    case "reverseEntityFieldLike":
                        return $this->caseInsensitiveLike($field, $value);
                    case "entityFieldExact":
                    case "reverseEntityFieldExact":
                        return $field . " = " . $this->toSQLStr($value);
                }
            };
            if (!is_null($value)) {
                if (gettype($value) == "array") {
                    $length = count($value);
                    foreach ($value as $k => $s) {
                        if (!is_null($s) && isset($s["id"])) {//check for empty values in the array
                            if ($k == 0) $out .= " (";
                            $last = $k + 1 == $length;
                            $clause = $buildClauses($s["id"]);
                            $out .= $last ? $clause . ") " : $clause .= " OR ";
                        }
                    }
                } else {
                    $out .= $buildClauses($value);
                }

            } else return false;
            return $out;
        }


        public
        function getAllSortAZ() {
            return $this->createQueryBuilder('m')
                ->orderBy('m.name', 'ASC')
                ->getQuery()
                ->getResult();
        }

        public
        function getAllSortId() {
            return $this->createQueryBuilder('m')
                ->orderBy('m.id', 'ASC')
                ->getQuery()
                ->getResult();
        }

        public function update($obj) {
            $em = $this->getEntityManager();
            $this->doctrineMergeService->merge($obj);
            $em->flush();
        }

        public function save(&$obj) {
            $em = $this->getEntityManager();
            $this->doctrineMergeService->merge($obj);
            $em->flush($obj);
        }

        private function validate($obj) {
            $errors = $this->validator->validate($obj);
            if (count($errors) > 0) throw new BadRequestHttpException((string)$errors);
        }

        public
        function delete(int $id) {
            $em = $this->getEntityManager();
            $em->remove($em->getReference($this->getClassName(), $id));
            $em->flush();
            return true;
        }

        /**
         * @param $dataRequested | as defined at @see RestService::dataRequested
         * @return array|null|object
         */
        public
        function get($dataRequested) {
            if ($dataRequested["pagination"]) {
                if(isset($dataRequested["previousJoins"]))
                    return $this->findAllPagWithPrejoins($dataRequested["pagination"], $dataRequested["sort"], $dataRequested["filter"], $dataRequested["previousJoins"]);
                else
                    return $this->findAllPag($dataRequested["pagination"], $dataRequested["sort"], $dataRequested["filter"]);
            } else return parent::find($dataRequested["id"]);
        }

        public
        function getById($id) {
            return parent::find($id);
        }


        public
        function getByNamePaginated(string $name, $pagination, $fields = []) {
            $dql = $this->createQueryBuilder("e")->select(array_merge(["e.id", "e.name"], $fields))
                ->where($this->caseInsensitiveLike("e.name", $name))
                ->orderBy("e.name")
                ->setFirstResult($pagination["page"] > 1 ? $pagination["page"] * $pagination["count"] : 0)->setMaxResults($pagination["count"])
                ->getQuery();
            $paginator = new Paginator($dql, true);
            return $dql->getArrayResult();
        }

        public function getReference($id, $entityName = "") {
            return $this->getEntityManager()->getReference($entityName ? $entityName : parent::getEntityName(), $id);
        }


        public function sql(string $query, $params = [], $retrieve = false) {
            $result = false;
            try {
                $stmt = $this->getEntityManager()->getConnection()->prepare($query);
                $result = $stmt->executeQuery($params);
                if ($retrieve) return $result->fetchAllAssociative();
            } catch (DBALException $e) {
            }
            return $result;
        }
    }