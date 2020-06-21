<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait DeleteApiViewMixin
{
    /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api delete view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     * )
     * @param $id
     * @return Response
     */
    public function deleteAction($id): Response
    {
        $service = $this->get($this->serviceClass);
        $filter = $this->commonFilter();
        $filter['id'] = $id;

        $entity = $service->get($filter);

        return $service->remove($entity) ?
            $this->success() : $this->warning();
    }
}
