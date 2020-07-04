<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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
    protected $dataClass;

    /**
     * CrudService constructor.
     * 
     * @param ContainerInterface $container
     * @param string $dataClass
     */
    function __construct(ContainerInterface $container, string $dataClass = null)
    {
        // init
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');

        if(empty($dataClass)) {
            // guess data class
            $className = (new \ReflectionClass($this))->getShortName();
            $classType = 'Service';
            $primaryName = '';
            $classPrefix = 'App/Entity/';
            $classSuffix = '';

            // end with 'Service'
            if(0 === substr_compare($className, $classType, -strlen($classType))) {
                $primaryName = str_replace($classType, '', $className);
            }
            else {
                throw new InternalErrorException('Cannot extends non-service class.');
            }

            $dataClass = $classPrefix . $primaryName . $classSuffix;
        }

        $this->dataClass = $dataClass;
        $this->rep = $this->em->getRepository($dataClass);
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
        /** Filters and orders */
        /*
        // GOT data sample
        $query = [
            '@order' => 'name|ASC, id|DESC'  // order by
            '@filter' => 'entity.id = 1 || entity.user = 10' // filter, attempt to add
        ];
        */

        // Get and parse request
        $request_stack = $this->container->get('request_stack');
        $request = $request_stack->getCurrentRequest();

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

        // Normal list
        if(is_array($object)) {
            $entities = $this->rep->findBy($object, $order);
        }
        else {
            // equal to findAll
            $entities = $this->rep->findBy([], $order);
        }

        // General filter
        if($filter = $request->query->get('@filter')) {
            $entities = array_filter($entities,
                function($entity) use ($filter) {
                    try {
                        $expressionLanguage = new ExpressionLanguage();
                        return $expressionLanguage->evaluate($filter, ['entity' => $entity]); 
                    }
                    catch(\Exception $e) {
                        return false;
                    }
                }
            );
        }

        return $entities;
    }


    /**
     * @return mixed
     */
    public function new()
    {
        return new $this->dataClass();
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
