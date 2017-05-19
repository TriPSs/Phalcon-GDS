<?php
/**
 * Created by PhpStorm.
 * User: tycho
 * Date: 04/05/2017
 * Time: 09:06
 */

namespace Phalcon\Datastore;

use Phalcon\Datastore\Model\ResultSet;
use Phalcon\Datastore\Query\Builder;
use Phalcon\Datastore\Query\Filter;
use Phalcon\Datastore\Query\OrderBy;
use GDS\Gateway\RESTv1;
use GDS\Store;
use GDS\Gateway\ProtoBuf;
use Phalcon\Di;

class Client extends Store {

    private $di;

    /**
     * Client constructor.
     *
     * @param Model  $model
     * @param string $namespace
     */
    public function __construct(Model $model, string $namespace = NULL) {
        $this->di = Di::getDefault();
        $config   = $this->di->get("config")->gcloud;
        $gateWay  = NULL;

        if (LOCAL) {
            $gateWay = new RESTv1($config->projectId, $namespace);

        } else {
            $gateWay = new ProtoBuf($config->projectId, $namespace);
        }

        $source = $model->getSource();

        parent::__construct($source, $gateWay);

        $this->setEntityClass(get_class($model));
    }

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

    private function convertToModel(Entity $entity) {
        return $entity->toModel();
    }

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