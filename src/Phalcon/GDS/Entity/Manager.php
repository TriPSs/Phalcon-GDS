<?php

namespace Phalcon\Datastore\Entity;

use Phalcon\Datastore\Entity;

class Manager {

    /**
     * @var Entity
     */
    private $entity;

    /**
     * Contains all the fields of the Model
     *
     * @var array
     */
    private $fields;

    /**
     * Manager constructor.
     *
     * @param Entity $entity
     */
    public function __construct(Entity $entity) {
        $this->entity = $entity;
    }

    /**
     * Returns all the fields of the Model
     *
     * @return mixed
     */
    public function getFields() {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        $reflectionClazz = new \ReflectionClass($this->entity);
        $props           = $reflectionClazz->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($props as $prop) {
            $this->fields[] = $prop->getName();
        }

        return $this->fields;
    }

    /**
     * Set's all the entity fields
     *
     * @param array $fields
     */
    public function setFields(array $fields) {
        $this->fields = $fields;
    }

    /**
     * Adds a field to the Model
     *
     * @param string $field
     */
    public function addField(string $field) {
        $this->fields[] = $field;
    }

    /**
     * Add fields to the model
     *
     * @param array $fields
     */
    public function addFields(array $fields) {
        $this->fields = array_merge($this->getFields(), $fields);
    }

}