<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash;

use Stash\Model\Model;
use Stash\Model\ModelException;

/**
 * Model collection
 *
 * @package Stash
 */
class ModelCollection
{
    use NormalizeNamespace;

    /**
     * @var ModelInterface[]
     */
    private $models = [];

    /**
     * @var ModelInterface[]
     */
    private $collections = [];

    /**
     * Constructor
     *
     * @param ModelInterface[] $models
     */
    public function __construct(array $models = [])
    {
        foreach ($models as $model) {
            $this->register($model);
        }
    }

    /**
     * Add model and associates it with Mongo collection
     *
     * @param ModelInterface $model
     */
    public function register(ModelInterface $model)
    {
        $this->models[$model->getClass()] = $model;

        if($model->getCollection() !== null) {
            $this->collections[$model->getCollection()] = &$this->models[$model->getClass()];
        }
    }

    /**
     * Get model by class name
     *
     * @param string $class
     *
     * @return ModelInterface
     * @throws ModelException
     */
    public function getByClass($class)
    {
        $class = $this->normalizeNamespace($class);

        if (isset($this->models[$class])) {
            return $this->models[$class];
        }

        throw new ModelException(sprintf('Model for "%s" not found', $class));
    }

    /**
     * Get model by instance class name
     *
     * @param object $instance
     *
     * @return ModelInterface
     * @throws ModelException
     */
    public function getByInstance($instance)
    {
        if (!is_object($instance)) {
            throw new ModelException(sprintf('Argument must be instance of object, got "%s"', gettype($instance)));
        }

        return $this->getByClass(get_class($instance));
    }

    /**
     * Get model by collection name
     *
     * @param string $collection
     *
     * @return ModelInterface
     * @throws ModelException
     */
    public function getByCollection($collection)
    {
        if (isset($this->collections[$collection])) {
            return $this->collections[$collection];
        }

        throw new ModelException(sprintf('Model with collection "%s" not found', $collection));
    }
}
