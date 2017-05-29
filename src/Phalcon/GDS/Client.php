<?php

namespace Phalcon\Datastore;

use Phalcon\Datastore\Model\ResultSet;
use Phalcon\Datastore\Query\Builder;
use GDS\Gateway\RESTv1;
use GDS\Store;
use GDS\Gateway\ProtoBuf;
use Phalcon\Di;
use Phalcon\Exception;

class Client extends Store {

    private $di;

    /**
     * Client constructor.
     *
     * @param Model       $model
     * @param string|NULL $namespace
     *
     * @throws Exception
     */
    public function __construct(Model $model, string $namespace = NULL) {
        $this->di = Di::getDefault();

        if (!$this->di->has("phalcon-gds")) {
            throw new Exception('No config found! Please use `$di->setShared("phalcon-gds")` to set your config!');
        }

        $config  = $this->di->get("phalcon-gds");
        $gateWay = NULL;

        if (isset($config->useRest) && $config->useRest) {
            $gateWay = new RESTv1($config->projectId, $namespace);

        } else {
            $gateWay = new ProtoBuf($config->projectId, $namespace);
        }

        $source = $model->getSource();

        parent::__construct($source, $gateWay);

        $this->setEntityClass(get_class($model));
    }

    /**
     * Fetch record by key
     *
     * @param $idOrName
     *
     * @return bool|ResultSet
     */
    public function fetchBy($idOrName) {
        if (is_numeric($idOrName)) {
            $result = $this->fetchById($idOrName);
        } else {
            $result = $this->fetchByName($idOrName);
        }

        if ($result) {
            return $this->convertToResultSet([ $result ]);
        }

        return FALSE;
    }

    /**
     * @param Builder|NULL $query
     *
     * @return ResultSet|bool
     */
    public function execute(Builder $query = NULL) {
        if (!is_null($query)) {
            $this->query($query->getQuery(), $query->getParams());
        }

        $result = $this->obj_gateway->withSchema($this->obj_schema)
                                    ->withTransaction($this->str_transaction_id)
                                    ->gql($this->str_last_query, $this->arr_last_params);

        $this->str_last_cursor = $this->obj_gateway->getEndCursor();

        return $this->convertToResultSet($result);
    }

    /**
     * Converts entity to model
     *
     * @param Entity $entity
     *
     * @return $this
     */
    private function convertToModel(Entity $entity) {
        return $entity->toModel();
    }

    /**
     * Converts the result to a result set
     *
     * @param $result
     *
     * @return bool|ResultSet
     */
    private function convertToResultSet($result) {
        $models = [];
        foreach ($result as $entity) {
            $models[] = $this->convertToModel($entity);
        }

        if (count($models) > 0) {
            return new ResultSet($models);
        }

        return FALSE;
    }

}