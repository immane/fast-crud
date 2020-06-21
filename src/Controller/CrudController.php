<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class CrudController extends AbstractController
{
    /**
     * Defining a Service Subscriber
     *
     * @return void
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(),
            [
                'knp_paginator' => PaginatorInterface::class,
            ]
        );
    }

    /**
     * @param [type] $collection
     * @return void
     */
    protected function pagination($collection)
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

    /**
     * Make translation for information messages
     * 
     * @param string $msg
     * @return void
     */
    private function trans(string $msg)
    {
        return $this->get('translator')->trans($msg);
    }

    /**
     * @param string $content
     * @param string $addition_message
     * @return void
     */
    protected function success($content = '', $addition_message = 'SUCCESS')
    {
        $serializer = $this->get("serializer");
        $paginatedContent = $this->pagination($content);
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

    /**
     * @param string $error_msg
     * @param integer $error_code
     * @param string $raw_data
     * @return void
     */
    protected function warning($error_msg = '', $error_code = -1, $raw_data = '')
    {
        $serializer = $this->get("serializer");
        $response = [
            'code' => $error_code ? : -1,
            'message'  => $this->trans($error_msg),
            'raw_data' => $raw_data,
        ];
        return new Response($serializer->serialize($response, 'json'), 200, array());
    }

    /**
     * @param Exception $exception
     * @return void
     */
    protected function error(\Exception $exception)
    {
        throw $exception;
    }
}
