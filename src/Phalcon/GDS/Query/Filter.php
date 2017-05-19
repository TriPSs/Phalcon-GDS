<?php
/**
 * Created by PhpStorm.
 * User: tycho
 * Date: 03/05/2017
 * Time: 17:42
 */

namespace Phalcon\Datastore\Query;

class Filter {

    protected $property;
    protected $operator;
    protected $value;

    public function __construct($property, $operator, $value, $space = NULL) {
        if (is_string($value) && !is_numeric($value)) {
            $value = "'$value'";
        }

        if ($property === "id" || $property === "__key__") {
            $property = "__key__";

            if (!is_null($space)) {
                $value = "KEY($space, $value)";
            }
        }

        $this->property = $property;
        $this->operator = $operator;
        $this->value    = $value;
    }

    public function getProperty() {
        return $this->property;
    }

    public function getOperator() {
        return $this->operator;
    }

    public function getValue() {
        return $this->value;
    }

    public function getQuery() {
        return $this->property . " " . $this->operator . " " . $this->value;
    }

}