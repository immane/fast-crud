<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

trait ListApiViewMixin
{
    protected function listFilter(array $filter = null): array
    {
        /** list filter for list entities */
        return $filter;
    }

    /**
     * @Route("", name="list", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Api list view",
     * )
     * @SWG\Parameter(name="page", in="query", type="string", description="Current page")
     * @SWG\Parameter(name="limit", in="query", type="string", description="Page limit")
     * @SWG\Parameter(name="@order", in="query", type="string", description="Database ordering")
     * @SWG\Parameter(name="@sort", in="query", type="string", description="Datasets ordering")
     * @SWG\Parameter(name="@filter", in="query", type="string", description="Datasets filter")
     * @SWG\Tag(name="list")
     * @Security(name="Bearer")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $service = $this->get($this->serviceClass);
        $filter =  $this->listFilter($this->commonFilter());
        $entities = $service->list($filter);
        return $this->success($entities);
    }
}
