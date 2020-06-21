<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\Serializer\Normalizer;

class CircularReferenceHandler
{
    public function __invoke($object){
        if (method_exists($object, 'getId')) {
            return $object->getId();
        }

        throw new \Exception('Every entity should have `getId` method');
    }
}