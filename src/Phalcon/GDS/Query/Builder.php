<?php
/**
 * Created by PhpStorm.
 * User: tycho
 * Date: 03/05/2017
 * Time: 17:42
 */

namespace Phalcon\Datastore\Query;

use Phalcon\Datastore\Model;

class Builder {

    private static $builder;

    private $query;

    /**
     * @var Model
     */
    private $model;

    private $orderBy     = "__KEY__ ASC";
    private $limit       = NULL;
    private $offset      = NULL;
    private $queryParams = [];

    private $hasWhere = FALSE;

    public static function init(Model $model) {
        if (self::$builder === NULL) {
            self::$builder = new self;
        }

        self::$builder->setQuery('SELECT * FROM `' . $model->getSource() . '`'); //  ORDER BY __key__ ASC';
        self::$builder->setModel($model);

        return self::$builder;
    }

    public function setQuery($query) {
        $this->query = $query;
    }

    public function setModel($model) {
        $this->model = $model;
    }

    public function getQuery() {
        if (!empty($this->orderBy)) {
            // $this->query .= " ORDER BY " . $this->orderBy;
        }


        if (!is_null($this->limit)) {
            $this->query .= " LIMIT " . $this->limit;
        }

        if (!is_null($this->offset)) {
            $this->query .= " OFFSET " . $this->offset;
        }

        return $this->query;
    }

    public function getParams() {
        return $this->queryParams;
    }

    /**
     * @param Filter $filter
     *
     * @return Builder
     */
    public function where(Filter $filter) {
        $this->addCondition($filter->getQuery(), !$this->hasWhere ? "WHERE" : "AND");

        $this->hasWhere = TRUE;

        return $this;
    }

    /**
     * @param Filter $filter
     *
     * @return Builder
     */
    public function andWhere(Filter $filter) {
        return $this->where($filter);
    }


    public function orderBy(OrderBy $orderBy) {
        $this->addCondition($orderBy->getQuery());

        return $this;
    }

    public function limit(int $limit) {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset) {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return Model\ResultSet|bool
     */
    public function execute() {
        $client = $this->model->getClient();

        return $client->execute($this);
    }

    private function addCondition($search, $prependWith = "AND") {
        $prependWith = strtoupper($prependWith);
        $this->query .= (empty($prependWith) ? " " : " $prependWith ") . $search;

        return $this;
    }
}