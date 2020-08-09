<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\View\Mixin;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;

trait CreateApiViewMixin
{
    /*
    protected $requiredCreateProperties = [];
    protected $acceptedCreateProperties = [];
    */

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

        // external content
        $content = json_decode($request->getContent(), true);

        // properties process.
        if(
            property_exists($this, 'requiredCreateProperties') ||
            property_exists($this, 'acceptedCreateProperties')
        ) {
            $data = [];

            if(property_exists($this, 'requiredCreateProperties')) {
                foreach ($this->requiredCreateProperties as $property) {
                    if (!array_key_exists($property, $content))
                        throw new ValidatorException(ucfirst($property) . " cannot be empty.");
                    $data[$property] = $content[$property];
                }
            }

            if(property_exists($this, 'acceptedCreateProperties')) {
                foreach ($this->acceptedCreateProperties as $property) {
                    if(array_key_exists($property, $content)) {
                        $data[$property] = $content[$property];
                    }
                }
            }

            $content = $data;
        }

        // process content
        $content = $this->processCreateContent(
            array_merge($content, $this->defaultCreateValues())
        );

        // save
        if ($entity = $service->update($entity, $content)) {
            return $this->Success($this->afterCreated($entity));
        } else {
            return $this->Warning();
        }
    }
}

