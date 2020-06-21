<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionInterceptor
{
    /** @var ContainerInterface */
    private $container;
    /** @var object|\Symfony\Component\Translation\DataCollectorTranslator|\Symfony\Component\Translation\IdentityTranslator */
    private $translator;
    /** @var object|\Symfony\Bridge\Monolog\Logger */
    private $logger;
    /** @var array */
    private $config;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->translator = $container->get('translator');
        $this->logger = $logger;
        $this->config = $container->getParameter('fast_crud.exception_interceptor');
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        // init
        $request = $event->getRequest();
        $exception = $event->getThrowable();
        $exceptionClassName = '';
        $exceptionRelectionClass = new \ReflectionClass($exception);
        if($exceptionRelectionClass) {
            $exceptionClassName = $exceptionRelectionClass->getShortName();
        }

        // get environment
        $env = $this->container->getParameter('kernel.environment');
        if('dev' === $env) {
            $this->logger->error(
                $event->getThrowable()->getMessage()
            );
        }

        // get config
        if (!(array_key_exists('enabled', $this->config) && $this->config['enabled']))
            throw $exception;

        if (array_key_exists('effective_pattern', $this->config)) {
            $effectivePattern = $this->config['effective_pattern'];
        } else {
            $effectivePattern = '/.*/';
        }

        // check is effective url
        $result = preg_match($effectivePattern, $request->getPathInfo());
        if (!$result) return;
 
        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        $response = new Response(
            $this->translator->trans($event->getThrowable()->getMessage())
        );

        // you can alternatively set a new Exception
        // $exception = new \Exception('Some special exception');
        // $event->setThrowable($exception);
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse(new Response(
            json_encode([
                "timestamp" => (new \DateTime())->format(\DateTime::RFC3339),
                "error" => $exceptionClassName,
                "code" => $exception->getCode() ?: -1,
                "message" => $exception->getMessage() ?: 'Unknow',
            ])
        ));
    }
}