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

use Stash\Converter\Type\ArrayType;
use Stash\Converter\Type\BooleanType;
use Stash\Converter\Type\DateType;
use Stash\Converter\Type\DocumentType;
use Stash\Converter\Type\DoubleType;
use Stash\Converter\Type\IdType;
use Stash\Converter\Type\IntegerType;
use Stash\Converter\Type\StringType;
use Stash\ConverterInterface;
use Stash\Fields;
use Stash\NormalizeNamespace;

/**
 * Type converter
 *
 * @package Stash
 */
final class Converter implements ConverterInterface
{
    use NormalizeNamespace;

    /**
     * @var TypeInterface[]
     */
    private $types = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->types[Fields::TYPE_ID] = new IdType();
        $this->types[Fields::TYPE_BOOLEAN] = new BooleanType();
        $this->types[Fields::TYPE_INTEGER] = new IntegerType();
        $this->types[Fields::TYPE_DOUBLE] = new DoubleType();
        $this->types[Fields::TYPE_STRING] = new StringType();
        $this->types[Fields::TYPE_DATE] = new DateType();
        $this->types[Fields::TYPE_ARRAY] = new ArrayType();
        $this->types[Fields::TYPE_DOCUMENT] = new DocumentType();
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
        $type = $this->normalizeNamespace($type);

        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        throw new UnknownTypeException(sprintf('Unknown type converter "%s"', $type));
    }
}
