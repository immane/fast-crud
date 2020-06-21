<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait SingleCreateAndUpdateApiViewMixin
{
    protected function defaultCreateValues(): array
    {
        /** Default values */
        return [];
    }

    protected function defaultUpdateValues(): array
    {
        /** Default values */
        return [];
    }

    /**
     * @Route("", name="update", methods={"PUT"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api single create and update view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     * )
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request): Response
    {
        $service = $this->get($this->serviceClass);
        $content = json_decode($request->getContent(), true) ? : [];

        $filter = $this->commonFilter();
        $entity = $service->get($filter);

        if(empty($entity)) {
            $entity = $service->new();
            $content = array_merge($content, $this->defaultCreateValues());
        }
        else {
            $content = array_merge($content, $this->defaultUpdateValues());
        }

        if ($entity = $service->update($entity, $content)) {
            return $this->success($entity);
        } else {
            return $this->warning();
        }
    }
}
