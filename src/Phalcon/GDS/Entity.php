<?php

namespace Phalcon\Datastore;

use Phalcon\Datastore\Events\Manager as EventManager;
use Phalcon\Datastore\Entity\Manager as EntityManager;

abstract class Entity extends \GDS\Entity {

    /**
     * Default field
     *
     * @var string
     */
    public $id;

    /**
     * Is the id auto filled by the generated id of Google
     *
     * @var bool
     */
    protected $autoFillId = TRUE;

    protected $entityManager;

    protected $eventManager;

    public function __construct() {
        $this->entityManager = new EntityManager($this);
        $this->eventManager  = new EventManager($this);
    }

    /**
     * Converts the Model and all it's data to an Entity
     *
     * @param array|NULL $data
     *
     * @return $this
     */
    public function toEntity(array $data = NULL) {
        $fields = $this->entityManager->getFields();

        foreach ($fields as $field) {
            if ($field !== "__key__" && $field !== "id") {
                if (empty($this->{$field}) && !empty($data[ $field ])) {
                    $this->{$field} = $data[ $field ];
                }

                if (!empty($this->{$field})) {
                    $this->__set($field, $this->{$field});
                }
            } else {
                $key = empty($this->{$field}) ? $data[ $field ] : $this->{$field};

                if (!empty($key)) {
                    if (is_numeric($key)) {
                        $this->setKeyId($key);

                    } else {
                        $this->setKeyName($key);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Converts a Entity and all it's data to an Model
     *
     * @return $this
     */
    public function toModel() {
        $modelData = $this->getData();

        if ($this->autoFillId && (!isset($modelData[ "id" ]) || empty($modelData[ "id" ]))) {
            if (!empty($this->getKeyId())) {
                $modelData[ "id" ] = $this->getKeyId();

            } else if (!empty($this->getKeyName())) {
                $modelData[ "id" ] = $this->getKeyName();
            }
        }

        foreach ($modelData as $column => $value) {
            $this->{$column} = $value;
        }

        $this->eventManager->fire("afterFetch");

        return $this;
    }

    public function getEntityManager(): EntityManager {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * Adds fields in the manager
     *
     * @param array $fields
     */
    public function addFields(array $fields) {
        $this->entityManager->addFields($fields);
    }

    public function setEventManager(EventManager $eventManager) {
        $this->eventManager = $eventManager;
    }

    public function getEventManager(): EventManager {
        return $this->eventManager;
    }
}