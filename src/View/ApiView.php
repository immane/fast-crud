<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View;

use Symfony\Component\CssSelector\Exception\InternalErrorException;

trait ApiView
{
    protected $serviceClass;

    public function __construct()
    {
        // Entity and service and controller must have the same primary name
        // eg. User, UserService and UserController

        // Override construct for custom class
        $className = (new \ReflectionClass($this))->getShortName();
        $classType = 'Controller';
        $primaryName = '';
        $serviceClassPrefix = 'App/Service/';
        $serviceClassSuffix = 'Service';

        // end with 'Controller'
        if(0 === substr_compare($className, $classType, -strlen($classType))) {
            $primaryName = str_replace($classType, '', $className);
        }
        else {
            throw new InternalErrorException('Cannot use api view in non-controller class.');
        }

        $serviceClass = $serviceClassPrefix . $primaryName . $serviceClassSuffix;
        $this->serviceClass = $serviceClass;
    }

    protected function commonFilter(): array
    {
        /** common filter for all entities */
        return [];
    }
}
