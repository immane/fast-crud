<?php
declare(strict_types=1);

namespace CommonBundle\Serializer\Callbacks;

class ObjectCallback
{
    public static function handle($object)
    {
        if ($object instanceof Object && method_exists($object, 'getId')) {
            return $object->getId();
        }
    }
}
