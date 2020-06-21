<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait UpdateApiViewMixin
{
    protected function defaultUpdateValues(): array
    {
        /** Default values */
        return [];
    }

    protected function processUpdateContent(array $content): array
    {
        /** Default values */
        return $content;
    }

    protected function afterUpdated($entity)
    {
        /** Updated entity */
        return $entity;
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"}, requirements={"id"="\d+"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api update view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     * )
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function updateAction(Request $request, $id): Response
    {
        $service = $this->get($this->serviceClass);
        $filter = $this->commonFilter();
        $filter['id'] = $id;
        $entity = $service->get($filter);

        $content = json_decode($request->getContent(), true) ? : [];
        $content = $this->processUpdateContent(
            array_merge($content, $this->defaultUpdateValues())
        );

        if ($entity = $service->update($entity, $content)) {
            return $this->success($this->afterUpdated($entity));
        } else {
            return $this->warning();
        }
    }
}

