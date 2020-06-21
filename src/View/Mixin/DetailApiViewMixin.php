<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait DetailApiViewMixin
{
    /**
     * @Route("/{id}", name="detail", methods={"GET"}, requirements={"id"="\d+"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api detail view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     * )
     * @param $id
     * @return Response
     */
    public function detailAction($id): Response
    {
        $service = $this->get($this->serviceClass);
        $filter = $id ? ['id' => $id]: [];
        $filter = array_merge($filter, $this->commonFilter());

        $entity = $service->get($filter);

        return $entity ?
            $this->success($entity):
            $this->warning('Entity is not found');
    }
}
