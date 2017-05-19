<?php

namespace Phalcon\Datastore\Events;

use Phalcon\Datastore\Entity;
use Phalcon\Events\ManagerInterface;

class Manager implements ManagerInterface {

    /**
     * @var Entity
     */
    private $entity;

    /**
     * Manager constructor.
     *
     * @param Entity $entity
     */
    public function __construct(Entity $entity) {
        $this->entity = $entity;
    }

    public function attach($eventType, $handler) { }

    public function detach($eventType, $handler) { }

    public function detachAll($type = NULL) { }

    /**
     * @param string $eventType
     * @param null   $source
     * @param array  $data
     *
     * @return bool
     */
    public function fire($eventType, $source = NULL, $data = []) {
        $source = !is_null($source) ? $source : $this->entity;

        if (method_exists($source, $eventType)) {
            return $source->{$eventType}($data);
        }

        return TRUE;
    }

    public function getListeners($type) { }

}