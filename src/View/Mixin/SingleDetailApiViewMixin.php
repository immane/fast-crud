<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait SingleDetailApiViewMixin
{
    /**
     * @Route("", name="detail", methods={"GET"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api single detail view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     * )
     * @return Response
     */
    public function detailAction(): Response
    {
        $service = $this->get($this->serviceClass);
        $filter = $this->commonFilter();

        $entity = $service->get($filter);

        return $this->success($entity);
    }
}
