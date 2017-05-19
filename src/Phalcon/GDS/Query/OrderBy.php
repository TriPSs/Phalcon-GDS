<?php

namespace Phalcon\Datastore\Query;

class OrderBy extends Filter {

    const ORDER_DEFAULT    = self::ORDER_ASCENDING;
    const ORDER_DESCENDING = "DESC";
    const ORDER_ASCENDING  = "ASC";

    private $direction;

    public function __construct($property, $direction = OrderBy::ORDER_DEFAULT) {
        parent::__construct($property, NULL, NULL);

        $this->direction = $direction;
    }

    public function getDirection() {
        return $this->direction;
    }

    public function getQuery() {
        return $this->property . " " . $this->direction;
    }

}