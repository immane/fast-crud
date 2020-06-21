<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
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
     * @Route("", name="update-create", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Api update create view",
     * )
     * @SWG\Tag(name="update-create")
     * @Security(name="Bearer")
     * 
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
