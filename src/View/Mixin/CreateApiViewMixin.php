<?php

namespace CommonBundle\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CreateApiViewMixin
{
    protected $serviceClass;

    protected function defaultCreateValues(): array
    {
        /** Default values */
        return [];
    }

    protected function processCreateContent(array $content): array
    {
        /** Default values */
        return $content;
    }

    protected function afterCreated($entity)
    {
        /** Created entity */
        return $entity;
    }

    /**
     * @Route("", name="create", methods={"POST"})
     * @ApiDoc(
     *  resource=true,
     *  description="Api create view",
     *  headers={
     *     {"name"="X-Auth-Token"}
     *  },
     * )
     * @param Request $request
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        $service = $this->get($this->serviceClass);
        $entity = $service->new();

        $content = json_decode($request->getContent(), true);
        $content = $this->processCreateContent(
            array_merge($content, $this->defaultCreateValues())
        );


        if ($entity = $service->update($entity, $content)) {
            return $this->success($this->afterCreated($entity));
        } else {
            return $this->warning();
        }
    }
}

