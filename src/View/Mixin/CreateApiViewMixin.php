<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
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
     * @SWG\Response(
     *     response=200,
     *     description="Api create view",
     * )
     * @SWG\Tag(name="create")
     * @Security(name="Bearer")
     * 
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

