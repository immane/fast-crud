<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

abstract class CrudService
{
    /** @var ContainerInterface */
    protected $container;
    /** @var \Doctrine\ORM\EntityManager|object */
    protected $em;
    /** @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository */
    protected $rep;
    /** @var string */
    protected $data_class;

    /**
     * CrudService constructor.
     * @param ContainerInterface $container
     * @param string $data_class
     */
    function __construct(ContainerInterface $container, string $data_class)
    {
        $this->container = $container;
        $this->data_class = $data_class;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->rep = $this->em->getRepository($data_class);
    }

    /**
     * @param $object
     * @return null|object
     */
    public function get($object)
    {
        if(is_array($object)) {
            return $this->rep->findOneBy($object);
        }
        else {
            return $this->rep->find($object);
        }
    }

    /**
     * @param null $object
     * @param null $order
     * @return array
     */
    public function list($object = null, $order = null): array
    {
        // Entity field parse.
        // Non-exist field in entities will be ignored.
        $entityFields = [];

        try {
            // Document reader.
            $docReader = new AnnotationReader();
            $reflect = new \ReflectionClass($this->data_class);

            // Get class information with reflection
            foreach ($reflect->getProperties() as $val) {
                $annotations = $docReader->getPropertyAnnotations($reflect->getProperty($val->name));
                foreach ($annotations as $annotation) {
                    // Normal type
                    // Note: type must be defined in Entity fields
                    if(property_exists($annotation, 'type')) {
                        $entityFields[$val->name] = [
                            'type' => $annotation->type,
                        ];

                        if(property_exists($annotation, 'options') &&
                            array_key_exists('comment', $annotation->options)) {
                            $entities[$val->name]['label'] = $annotation->options['comment'];
                        }
                    }

                    // Accept ManyToOne type
                    elseif($annotation instanceof ManyToOne) {
                        $entityFields[$val->name] = [
                            'type' => 'ManyToOne',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {}


        // Define getter closure
        $g = function($object, $key) {
            $getter = 'get'.ucfirst($key);
            $object = $object->$getter();

            // when object is a entity relations
            if(method_exists($object, 'getId')) {
                return $object->getId();
            }

            // default
            return $object;
        };

        // Translate data to target
        $t = function($object, $k) {
            // Income object is DateTime format.
            if($object instanceof \DateTime) {
                return new \DateTime($k);
            }

            // default
            return $k;
        };

        /* Condition function configuration
           $o : Object
           $k : Key
           $d : Data
           $g : Object Getter
           $t : Data Translator
        */
        $conditionFunctions = [
            '<='   => function($o, $k, $d) use ($g, $t) { return $g($o, $k) <= $t($g($o, $k), $d); },   // less or equal
            '<'    => function($o, $k, $d) use ($g, $t) { return $g($o, $k) <  $t($g($o, $k), $d); },   // less
            '>='   => function($o, $k, $d) use ($g, $t) { return $g($o, $k) >= $t($g($o, $k), $d); },   // grater or equal
            '>'    => function($o, $k, $d) use ($g, $t) { return $g($o, $k) >  $t($g($o, $k), $d); },   // grater
            '='    => function($o, $k, $d) use ($g, $t) { return $g($o, $k) == $t($g($o, $k), $d); },   // equal
            '=='   => function($o, $k, $d) use ($g, $t) { return $g($o, $k) == $t($g($o, $k), $d); },   // equal
            '!='   => function($o, $k, $d) use ($g, $t) { return $g($o, $k) != $t($g($o, $k), $d); },   // not equal
            '~'    => function($o, $k, $d) use ($g, $t) { return strpos($g($o, $k) ?: '', $d) !== false; },   // like
        ];

        // Setup filter, Match regex

        /*
        // Filter structure
        $filters = [
            'entityName' => [
                'data' => $data,
                'function' => $function,
            ]
        ];

        // GET data sample
        $query = [
            'id' => 1,              // Integer
            'name' => '~ Rin'       // String, fuzzy matching
            'type' => 1,            // Entity, Many-to-one
            'createTime' => '> yesterday midnight', // DateTime,
            'modifiedTime' => '<= 2020-01-01'       // DateTime,
            '@order' => 'name|ASC, id|DESC'  // order by
            '@filter' => 'entity.id = 1 || entity.user = 10' // filter, attempt to add
        ];
        */
        $filters = [];
        $conditionPattern = "/^\s*(\>\=|\<\=|\>|\=|\<|\~)\s*(.+)$/";

        // get and parse request
        $request_stack = $this->container->get('request_stack');
        $request = $request_stack->getCurrentRequest();
        foreach ($request->query as $key => $value) {

            if(array_key_exists($key, $entityFields)) {
                $matches = [];
                $result = preg_match_all($conditionPattern, $value, $matches);

                if($result == 0 /* equal */) {
                    $conditionSign = '=';
                    $conditionData = $value;
                }
                elseif($result == 1 /* matches */) {
                    $conditionSign = $matches[1][0]; // match first bracket
                    $conditionData = $matches[2][0]; // match second bracket
                }
                else continue;

                // translate input filter data to a filter array.
                if(array_key_exists($conditionSign, $conditionFunctions)) {
                    $filters[$key] = [
                        'data' => $conditionData,
                        'function' => $conditionFunctions[$conditionSign],
                    ];
                }
            }
        }

        // Replace order
        if($preOrders = $request->query->get('@order')) {
            $preOrders = explode(',', trim($preOrders));
            $order = [];

            foreach ($preOrders as $o) {
                $t = explode('|', $o);
                if(count($t) == 2) {
                    $order[trim($t[0])] = trim($t[1]);
                }
            }
        }

        // Get entities

        // Normal list
        if(is_array($object)) {
            $entities = $this->rep->findBy($object, $order);
        }
        else {
            // equal to findAll
            $entities = $this->rep->findBy([], $order);
        }

        $entities = array_filter($entities,
            function($entity) use ($filters) {
                foreach ($filters as $key => $filter) {
                    // Load filter data and apply filter function
                    if(!$filter['function']($entity, $key, $filter['data']) ) return false;
                }
                return true;
            }
        );

        return $entities;
    }


    /**
     * @return mixed
     */
    public function new()
    {
        return new $this->data_class();
    }

    /**
     * @param $object
     * @param $data
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function update(&$object, array $data = null)
    {
        if(!empty($data)) {
            $serializer = $this->container->get('serializer');
            $docReader = new AnnotationReader();

            try {
                $reflect = new \ReflectionClass(get_class($object));

                foreach ($data as $key => $val) {
                    if (!$reflect->hasProperty($key) /*|| !is_numeric($val)*/) {
                        // the entity does not have a such property
                        continue;
                    }
                    $annotations = $docReader->getPropertyAnnotations($reflect->getProperty($key));
                    foreach ($annotations as $annotation) 
                    {
                        if($annotation instanceof ManyToOne ||
                           $annotation instanceof OneToOne) {
                            $dataClass = $annotation->targetEntity;
                            $rep = $this->em->getRepository($dataClass);

                            $entity = null;
                            if($val && empty($entity = $rep->find($val))) {
                                throw new NotFoundHttpException('The entity is not found');
                            }
                            else {
                                $setter = 'set'.ucfirst($key);
                                $object->$setter($entity);

                                // delete current value
                                unset($data[$key]);
                            }
                            break;
                        }
                        else {
                            if($annotation->type === 'datetime' ||
                               $annotation->type === 'date' || 
                               $annotation->type === 'time' ) {
                                $setter = 'set'.ucfirst($key);
                                $object->$setter(new \DateTime($val));

                                // delete current value
                                unset($data[$key]);
                            }
                        }
                    }
                }
            } catch (\ReflectionException $e) {
                return false;
            }

            // de-normalize json to object.
            $serializer->deserialize(json_encode($data), get_class($object), 'json', ['object_to_populate' => $object]);
        }

        $validator = $this->container->get('validator');
        $errors = $validator->validate($object);
        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string) $errors;
            throw new ValidatorException($errorsString);
        }

        $this->em->persist($object);
        $this->em->flush();

        return $object;
    }

    /**
     * @param $object
     * @return bool
     * @throws ORMException
     */
    public function remove($object): bool
    {
        $object = $this->get($object);

        $this->em->remove($object);
        try {
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
