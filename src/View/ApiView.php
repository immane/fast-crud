<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View;

trait ApiView
{
    protected $serviceClass;

    protected function commonFilter(): array
    {
        /** common filter for all entities */
        return [];
    }
}
