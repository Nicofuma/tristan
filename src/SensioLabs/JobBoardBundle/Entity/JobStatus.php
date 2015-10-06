<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Biplane\EnumBundle\Enumeration\Enum;

class JobStatus extends Enum
{
    const NEW_JOB = 'new';
    const PUBLISHED = 'published';
    const ARCHIVED = 'archived';
    const DELETED = 'deleted';
    const RESTORED = 'restored';

    public static function getPossibleValues()
    {
        return [
            static::NEW_JOB,
            static::PUBLISHED,
            static::ARCHIVED,
            static::DELETED,
            static::RESTORED,
        ];
    }

    public static function getReadables()
    {
        return [
            static::NEW_JOB => 'New',
            static::PUBLISHED => 'Published',
            static::ARCHIVED => 'Archived',
            static::DELETED => 'Deleted',
            static::RESTORED => 'Restored',
        ];
    }
}
