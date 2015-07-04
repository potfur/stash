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

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 * Lazy loading reference resolver
 *
 * @package Stash
 */
final class ReferenceResolver implements ReferenceResolverInterface
{
    const REFERENCE_ID = '$id';
    const REFERENCE_REF = '$ref';

    /**
     * @var ModelCollection
     */
    private $models;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $factory;

    /**
     * Constructor
     *
     * @param ModelCollection $models
     */
    public function __construct(ModelCollection $models)
    {
        $this->models = $models;
        $this->factory = new LazyLoadingValueHolderFactory();
    }

    /**
     * Link referencer with connection for resolving references
     *
     * @param Connection $connection
     */
    public function connect(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Store reference in database format
     *
     * @param object $entity
     *
     * @return array
     * @throws InvalidEntityException
     */
    public function store($entity)
    {
        if ($entity instanceof VirtualProxyInterface) {
            return $this->getFromProxy($entity);
        }

        return $this->getFromEntity($entity);
    }

    /**
     * Create reference from proxy
     *
     * @param VirtualProxyInterface $proxy
     *
     * @return array
     * @throws InvalidEntityException
     */
    private function getFromProxy(VirtualProxyInterface $proxy)
    {
        if (!$proxy->isProxyInitialized()) {
            $proxy->initializeProxy();
        }

        return $this->getFromEntity($proxy->getWrappedValueHolderValue());
    }

    /**
     * Create reference from entity
     *
     * @param null|object $entity
     *
     * @return array
     * @throws InvalidEntityException
     * @throws \Stash\Model\ModelException
     */
    private function getFromEntity($entity)
    {
        if ($entity === null) {
            return null;
        }

        $reflection = new \ReflectionClass($entity);
        if (!$reflection->hasProperty(Fields::KEY_ID)) {
            throw new InvalidEntityException(sprintf('Entity does not have "%s" field', Fields::KEY_ID));
        }

        $id = $reflection->getProperty(Fields::KEY_ID)->getValue($entity);
        if (empty($id)) {
            throw new InvalidEntityException('Entity identifier is empty');
        }

        $model = $this->models->getByInstance($entity);

        return [
            self::REFERENCE_REF => $model->getCollection(),
            self::REFERENCE_ID => $id
        ];
    }

    /**
     * Resolve reference from database format
     *
     * @param array $reference
     *
     * @return null|object
     * @throws InvalidReferenceException
     */
    public function resolve($reference)
    {
        if ($reference === null) {
            return null;
        }

        $this->assertReference($reference);
        $this->assertConnection($this->connection);

        $proxy = $this->factory->createProxy(
            $this->models->getByCollection($reference[self::REFERENCE_REF])->getClass(),
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($reference) {
                $initializer = null;
                $wrappedObject = $this->connection
                    ->getCollection($reference[self::REFERENCE_REF])
                    ->findById($reference[self::REFERENCE_ID]);
            }
        );

        return $proxy;
    }

    /**
     * Assert proper reference structure
     *
     * @param array $reference
     *
     * @throws InvalidReferenceException
     */
    private function assertReference($reference)
    {
        if (!is_array($reference) || !array_key_exists(self::REFERENCE_ID, $reference) || !array_key_exists(self::REFERENCE_REF, $reference)) {
            throw new InvalidReferenceException('Invalid reference array');
        }
    }

    /**
     * Assert if resolver is connected
     *
     * @param Connection $connection
     *
     * @throws InvalidReferenceException
     */
    private function assertConnection($connection)
    {
        if (!$connection) {
            throw new InvalidReferenceException('Unable to resolve reference, not connected');
        }
    }
}
