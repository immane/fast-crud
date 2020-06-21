<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait ListApiViewMixin
{
    protected function listFilter(array $filter = null): array
    {
        /** list filter for list entities */
        return $filter;
    }

    /**
     * @Route("", name="list", methods={"GET"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api list view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     *  parameters={
     *     {"name"="page", "dataType"="string", "required"=false},
     *     {"name"="limit", "dataType"="string", "required"=false},
     *     {"name"="@order", "dataType"="string", "required"=false},
     *     {"name"="@filter", "dataType"="string", "required"=false},
     *  },
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $service = $this->get($this->serviceClass);
        $filter =  $this->listFilter($this->commonFilter());
        $entities = $service->list($filter);
        return $this->success($entities);
    }
}
