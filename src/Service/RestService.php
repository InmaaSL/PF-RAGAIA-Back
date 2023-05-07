<?php

    namespace App\Service;


    use Symfony\Component\HttpFoundation\Request;

    class RestService {
        /** Extract what front is asking from params an url part
         * @param Request $request
         * @return mixed | array containing id pagination and sort options
         */
        public function getRequestedData(Request $request) {
            $queryParams = $request->query->all();
            $dataRequested["id"] = (int)$request->attributes->get("id");
            if(!isset($queryParams["p"]) || !isset($queryParams["c"])) {
                $dataRequested["pagination"] = false;
            }
            else {
                $dataRequested["pagination"] = $this->getPag($queryParams);
            }
            $dataRequested["sort"] = $this->getFromJson("s", $queryParams);
            $dataRequested["filter"] = $this->getFromJson("f", $queryParams);
            return $dataRequested;
        }

        /**
         * @param $decodeFrom
         * @param $queryParams
         * @return bool|mixed
         */
        private function getFromJson($decodeFrom, $queryParams) {
            return !isset($queryParams[$decodeFrom]) ? false : json_decode($queryParams[$decodeFrom], true);
        }


        public function getPagination(Request $request) {
            $queryParams = $request->query->all();
            return $this->getPag($queryParams);
        }

        private function getPag($queryParams) {
            $pagination["page"] = $queryParams["p"];
            if ($queryParams["c"] > 2050) {
                throw new \Error("count to high for paginated results");
            }
            $pagination["count"] = $queryParams["c"];
            return $pagination;
        }
    }