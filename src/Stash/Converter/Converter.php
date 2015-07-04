<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Converter;

use Stash\ConverterInterface;

/**
 * Type converter
 *
 * @package Stash
 */
final class Converter implements ConverterInterface
{
    /**
     * @var TypeInterface[]
     */
    private $types = [];

    /**
     * Constructor
     *
     * @param array $types
     */
    public function __construct(array $types = [])
    {
        foreach ($types as $type) {
            $this->addType($type);
        }
    }

    /**
     * Add type to converter
     *
     * @param TypeInterface $type
     */
    public function addType(TypeInterface $type)
    {
        $this->types[$type->getType()] = $type;
    }

    /**
     * Convert a value from its PHP representation
     *
     * @param mixed  $value The value to convert.
     * @param string $type
     *
     * @return mixed
     * @throws UnknownTypeException
     */
    public function convertToDatabaseValue($value, $type)
    {
        return $this->getConverter($type)->convertToDatabaseValue($value);
    }

    /**
     * Convert a value from its database representation
     *
     * @param mixed  $value The value to convert.
     * @param string $type
     *
     * @return mixed
     * @throws UnknownTypeException
     */
    public function convertToPHPValue($value, $type)
    {
        return $this->getConverter($type)->convertToPHPValue($value);
    }

    /**
     * Return converter for type
     *
     * @param string $type
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    private function getConverter($type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        throw new UnknownTypeException(sprintf('Unknown type converter "%s"', $type));
    }
}
