<?php

namespace Phalcon\Datastore\Model;

use Phalcon\Datastore\Model;
use Iterator;

class ResultSet implements Iterator {

    /**
     * Current pusition
     *
     * @var int
     */
    private $position = 0;

    private $result;

    private $size;

    /**
     * All valid fields of the Entity
     *
     * @var
     */
    private $fields;

    /**
     * ResultSet constructor.
     *
     * @param array $result
     */
    public function __construct(array $result) {
        $this->result = $result;
        $this->size   = count($result);
    }

    /**
     * Returns the first result in the set or false when there is no result
     *
     * @return bool|mixed
     */
    public function getFirst() {
        if (count($this->result) > 0) {
            $model = $this->result[ 0 ];

            if (!empty($this->fields)) {
                $model->addFields($this->fields);
            }

            return $model;
        }

        return FALSE;
    }

    /**
     * @return Model
     */
    public function getLast() {
        return $this->result[ $this->size - 1 ];
    }

    /**
     * Converts the result set to a array
     *
     * @return array
     */
    public function toArray() {
        $resultArray = [];

        foreach ($this->result as $model) {
            if (!empty($this->fields)) {
                $model->addFields($this->fields);
            }

            $resultArray[] = $model->toArray();
        }

        return $resultArray;
    }

    /**
     * @return Model
     */
    public function current() {
        return $this->result[ $this->position ];
    }

    public function next() {
        $this->position++;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return $this->position < $this->size;
    }

    public function rewind() {
        $this->position = 0;
    }

    /**
     * Set the valid fields
     *
     * @param array $fields
     */
    public function setFields(array $fields) {
        $this->fields = $fields;
    }

    /**
     * Add valid fields
     *
     * @param array $fields
     */
    public function addFields(array $fields) {
        if (empty($this->fields)) {
            $this->setFields($fields);

        } else {
            $this->fields = array_merge($this->fields, $fields);
        }
    }
}