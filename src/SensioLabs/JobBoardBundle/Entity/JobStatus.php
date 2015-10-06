<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Biplane\EnumBundle\Enumeration\Enum;

class JobStatus extends Enum
{
    const NEW_JOB = 'new';
    const ORDERED = 'ordered';
    const PUBLISHED = 'published';
    const EXPIRED = 'expired';
    const ARCHIVED = 'archived';
    const DELETED = 'deleted';
    const RESTORED = 'restored';

    public static function getPossibleValues()
    {
        return [
            static::NEW_JOB,
            static::ORDERED,
            static::PUBLISHED,
            static::EXPIRED,
            static::ARCHIVED,
            static::DELETED,
            static::RESTORED,
        ];
    }

    public static function getReadables()
    {
        return [
            static::NEW_JOB => 'New',
            static::ORDERED => 'Ordered',
            static::PUBLISHED => 'Published',
            static::EXPIRED => 'Expired',
            static::ARCHIVED => 'Archived',
            static::DELETED => 'Deleted',
            static::RESTORED => 'Restored',
        ];
    }
}
