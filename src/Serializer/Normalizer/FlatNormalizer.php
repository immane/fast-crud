<?php

namespace RinProject\FastCrudBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class FlatNormalizer extends GetSetMethodNormalizer
{
    protected function getAttributeValue($object, $attribute, $format = null, array $context = array())
    {
        $ucfirsted = ucfirst($attribute);

        $getter = 'get'.$ucfirsted;
        if (\is_callable(array($object, $getter))) {

            $object = $object->$getter();

            // when object is a relations
            if (method_exists($object, 'getId')) {
                $res = [];
                $res['id'] = $object->getId();
                if (method_exists($object, '__toString'))
                    $res['__toString'] = $object->__toString();
                if (method_exists($object, '__metadata'))
                    $res['__metadata'] = $object->__metadata();

                return $res;
            }

            // when object is array collections
            elseif ($object instanceof \Traversable) {
                $tmp_object = [];
                foreach ($object as $o) {
                    if (method_exists($o, 'getId')) {
                        $res = [];
                        $res['id'] = $o->getId();
                        if (method_exists($o, '__toString'))
                            $res['__toString'] = $o->__toString();
                        if (method_exists($o, '__metadata'))
                            $res['__metadata'] = $o->__metadata();

                        $tmp_object[] = $res;
                    }
                }
                return $tmp_object;
            }

            // normal objects
            else {
                return $object;
            }
        }

        $isser = 'is'.$ucfirsted;
        if (\is_callable(array($object, $isser))) {
            return $object->$isser();
        }

        $haser = 'has'.$ucfirsted;
        if (\is_callable(array($object, $haser))) {
            return $object->$haser();
        }
    }
}
