<?php

namespace Phalcon\Datastore;

use Phalcon\Datastore\Query\Builder;
use GDS\Mapper;
use Phalcon\Di;

abstract class Model extends Entity {

    private $di;

    private static $model;

    private static $modelClazz;

    private $client;

    /**
     * Namespace to save/update/delete/get the data in
     *
     * @var null
     */
    protected $namespace = NULL;

    /**
     * Fields that should not return in the toArray function
     *
     * @var array
     */
    protected $removeFields = [];

    /**
     * Model constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->di = Di::getDefault();

    }

    public static function builder($namespace = NULL) {
        $model = self::getModel($namespace);

        return Builder::init($model);
    }

    public static function findFirst(Builder $query = NULL) {
        return self::find($query)->getFirst();
    }

    public static function find(Builder $query = NULL) {
        $model  = self::getModel();
        $client = $model->getClient();

        return $client->execute($query);
    }

    public static function findFirstById($id) {
        $result = self::findById($id);

        return $result ? $result->getFirst() : $result;
    }

    public static function findById($id) {
        $model  = self::getModel();
        $client = $model->getClient();

        return $client->fetchBy($id);
    }

    public static function findByIds(array $ids) {
        $model  = self::getModel();
        $client = $model->getClient();

        return $client->fetchByIds($ids);
    }

    public static function createEntity(array $fields) {
        $model = self::getModel();

        return $model->create($fields);
    }

    public function create(array $data = NULL) {
        if ($this->eventManager->fire("beforeCreate") === FALSE) {
            return FALSE;
        }

        $this->setMode(Entity::MODE_INSERT);
        $this->getClient()->upsert($this->toEntity($data));

        $this->eventManager->fire("afterCreate");

        return TRUE;
    }

    public function update(array $data = NULL) {
        if ($this->eventManager->fire("beforeUpdate") === FALSE) {
            return FALSE;
        }

        $this->setMode(Entity::MODE_UPDATE);
        $this->getClient()->upsert($this->toEntity($data));

        $this->eventManager->fire("afterUpdate");

        return TRUE;
    }

    public function delete() {
        if ($this->eventManager->fire("beforeDelete") === FALSE) {
            return FALSE;
        }

        $deleted = $this->getClient()->delete($this);

        $this->eventManager->fire("afterDelete");

        return $deleted;
    }

    /**
     * Converts the Model to a array
     *
     * @param null $columns
     *
     * @return array
     */
    public function toArray($columns = NULL): array {
        if (is_null($columns)) {
            $columns = $this->entityManager->getFields();
        }

        $array = [];
        foreach ($columns as $column) {
            if (!empty($this->{$column}) && !in_array($column, $this->removeFields)) {
                if ($this->{$column} instanceof \DateTime) {
                    $array[ $column ] = $this->{$column}->format(Mapper::DATETIME_FORMAT_V2);

                } else {
                    $array[ $column ] = $this->{$column};
                }

            } else if ($column !== "id" || !$this->autoFillId) {
                $array[ $column ] = NULL;
            }
        }

        return $array;
    }

    public function getSource() {
        return NULL;
    }

    private static function getModel($namespace = NULL): Model {
        $modelClazz = get_called_class();
        if (empty(self::$model) || self::$modelClazz !== $modelClazz) {
            self::$model = new $modelClazz();

            if (!is_null($namespace)) {
                self::$model->setNamespace($namespace);
            }
        }

        return self::$model;
    }

    public function getClient(): Client {
        if (empty($this->client)) {
            $this->client = new Client($this, $this->getNamespace());
        }

        return $this->client;
    }

    public static function client(): Client {
        return self::getModel()->getClient();
    }

    /**
     * Set's if the id is auto filled by the generated id from Google
     *
     * @param bool $autoFillId
     */
    public function setAutoFillId(bool $autoFillId) {
        $this->autoFillId = $autoFillId;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function setNamespace(string $namespace) {
        $this->namespace = $namespace;
    }

    protected function getDI() {
        return $this->di;
    }
}