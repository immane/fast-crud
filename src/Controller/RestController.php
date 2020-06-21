<?php

namespace RinProject\FastCrudBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class RestController extends AbstractController
{
    // Defining a Service Subscriber
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(),
            [
                'knp_paginator' => PaginatorInterface::class,
            ]
        );
    }

    protected function Pagination($collection)
    {
        // get current request
        $request_stack = $this->get('request_stack');
        $request = $request_stack->getCurrentRequest();

        if($collection && (is_array($collection) || $collection instanceof ArrayCollection)) {
            $pager = $this->get('knp_paginator');

            if($request->getMethod() === 'GET')  {
                $paginated = $pager->paginate( $collection,
                    $request->query->getInt('page', 1),
                    $request->query->getInt('limit', PHP_INT_MAX));
            }
            else {
                $paginated = $collection;
            }
            return $paginated;
        }
        else {
            return $collection;
        }
    }

    private function trans(string $msg)
    {
        return $this->get('translator')->trans($msg);
    }

    protected function Success($content = '', $addition_message = 'SUCCESS')
    {
        $serializer = $this->get("serializer");
        $paginatedContent = $this->Pagination($content);
        $response = [
            'data' => $paginatedContent,
            'code' => 0,
            'message'  => $addition_message,
        ];
        if($paginatedContent instanceof AbstractPagination) {
            $response['paginator'] = $paginatedContent->getPaginationData();
        }
        return new Response($serializer->serialize($response, 'json'), 200, array());
    }

    protected function Warning($error_msg = '', $error_code = -1, $raw_data = '')
    {
        $serializer = $this->get("serializer");
        $response = [
            'code' => $error_code ? : -1,
            'message'  => $this->trans($error_msg),
            'raw_data' => $raw_data,
        ];
        return new Response($serializer->serialize($response, 'json'), 200, array());
    }

    protected function Error($error_msg = 'Service unavailable.', $error_status = 500)
    {
        return new Response($this->trans($error_msg), $error_status, array());
    }
}
