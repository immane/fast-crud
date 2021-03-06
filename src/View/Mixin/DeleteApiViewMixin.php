<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait DeleteApiViewMixin
{
    /**
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @SWG\Response(
     *     response=200,
     *     description="Api delete view",
     * )
     * @SWG\Tag(name="delete")
     * @Security(name="Bearer")
     * 
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
