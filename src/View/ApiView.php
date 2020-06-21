<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait ApiView
{
    protected $serviceClass;

    protected function commonFilter(): array
    {
        /** common filter for all entities */
        return [];
    }
}
