<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait SingleRetrieveApiViewMixin
{
    /**
     * @Route("", name="retrieve", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Api retrieve view",
     * )
     * @SWG\Tag(name="retrieve")
     * @Security(name="Bearer")
     * 
     * @return Response
     */
    public function retrieveAction(): Response
    {
        $service = $this->get($this->serviceClass);
        $filter = $this->commonFilter();

        $entity = $service->get($filter);

        return $this->success($entity);
    }
}
