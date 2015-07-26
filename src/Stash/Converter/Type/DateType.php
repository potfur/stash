<?php

/*
* This file is part of the Stash package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Stash\Converter\Type;

use Stash\Converter\TypeInterface;
use Stash\Fields;

/**
 * Date converter
 *
 * @property \DateTimeZone timezone
 * @package Stash
 */
final class DateType implements TypeInterface
{
    /**
     * @var \DateTimeZone
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param \DateTimeZone|null $timezone
     */
    public function __construct(\DateTimeZone $timezone = null)
    {
        if ($timezone !== null) {
            $this->setTimezone($timezone);
        }
    }

    /**
     * Set timezone for all read dates
     *
     * @param \DateTimeZone $timezone
     */
    public function setTimezone(\DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Return type name
     *
     * @return string
     */
    public function getType()
    {
        return Fields::TYPE_DATE;
    }

    /**
     * Convert a value from its PHP representation.
     *
     * @param \DateTime $value
     *
     * @return \MongoDate
     */
    public function convertToDatabaseValue($value)
    {
        return $value === null ? null : new \MongoDate($value->getTimestamp(), $this->timezone);
    }

    /**
     * Convert a value from its database representation.
     *
     * @param \MongoDate $value
     *
     * @return \DateTime
     */
    public function convertToPHPValue($value)
    {
        return $value === null ? null : (new \DateTime())->setTimestamp($value->sec);
    }
}
