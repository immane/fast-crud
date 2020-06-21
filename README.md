FastCurdBundle
==============


Prerequisites
-------------

*	PHP 7.1.3 or higher
*	Symfony 5.0 or higher


Testing Environment
-------------------
*   Mac OSX 10.15 Catalina
*   PHP 7.2.7
*   Symfony 5.1

Installation
------------

Open a command console, enter your project directory and install *fast-crud* from composer:

    $ composer require rin-project/fast-crud


Add bundle config to *App\Kernel.php* for registring bundle if you're not using Flex:

    return [
        // ...

        // add this line
        RinProject\FastCrudBundle\FastCrudBundle::class => ['all' => true],
    ];

Import default config in *config/packages/framework.yaml*:

    # config/packages/framework.yaml
    imports:
        - { resource: "@FastCrudBundle/Resources/config/config.yaml" }

Import default routes in *config/routes.yaml* (optional):

    # config/routes.yaml
    fast-crud-route:
        resource: "@FastCrudBundle/Resources/config/routes.yaml"

If you want to catch all exceptions automatics, enable exception interceptor in config file *config/packages/framework.yaml*:

    # config/packages/framework.yaml
    fast_crud:
        exception_interceptor:
            enabled: true
            effective_pattern: /^\/(api|manage)\/.*$/

Usage
-----

Create a Fast-CRUD service:

    namespace App\Service;

    use App\Entity\User;
    use RinProject\FastCrudBundle\Service\CrudService;

    final class UserService extends CrudService
    {
        function __construct(ContainerInterface $container)
        {
            parent::__construct($container, User::class);
        }
    }

Create a controller inherit CrudController:

    namespace App\Api\Controller;

    use App\Service\UserService;
    use RinProject\FastCrudBundle\Service\CrudService;
    use RinProject\FastCrudBundle\View\ApiView;
    use RinProject\FastCrudBundle\View\Mixin\SingleCreateAndUpdateApiViewMixin;
    use RinProject\FastCrudBundle\View\Mixin\SingleRetrieveApiViewMixin;

    /**
     * @Route("/api/user", name="api-user-")
     */
    class UserController extends CrudController
    {
        use ApiView, SingleRetrieveApiViewMixin, SingleCreateAndUpdateApiViewMixin;

        public function __construct()
        {
            $this->serviceClass = UserService::class;
        }

        public function commonFilter()
        {
            return ['id' => $this->getUser()];
        }
    }


TO BE CONTINUE ...
---