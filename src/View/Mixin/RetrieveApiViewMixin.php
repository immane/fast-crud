<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait RetrieveApiViewMixin
{
    /**
     * @Route("/{id}", name="retrieve", methods={"GET"}, requirements={"id"="\d+"})
     * @SWG\Response(
     *     response=200,
     *     description="Api retrieve view",
     * )
     * @SWG\Tag(name="retrieve")
     * @Security(name="Bearer")
     * 
     * @param $id
     * @return Response
     */
    public function retrieveAction($id): Response
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
