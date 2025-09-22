<?php declare(strict_types=1);

namespace App\Modules\ApiDoc;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route as AttributeRoute;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;

class Controller extends AbstractController
{
    public function __construct(private RouterInterface $router) {}

    #[AttributeRoute('/api/doc', methods: ['GET'])]
    public function apiDoc(): Response
    {
        $routes = [];
        foreach ($this->router->getRouteCollection() as $name => $route) {
            /** @var Route $route */
            $controllerInfo = null;
            $requestExample = null;
            $defaults = $route->getDefaults();
            if (isset($defaults['_controller'])) {
                $controllerParts = explode('::', $defaults['_controller']);
                if (count($controllerParts) === 2) {
                    $controllerClass = $controllerParts[0];
                    $controllerMethod = $controllerParts[1];
                    if (class_exists($controllerClass) && method_exists($controllerClass, $controllerMethod)) {
                        $reflection = new \ReflectionMethod($controllerClass, $controllerMethod);
                        foreach ($reflection->getParameters() as $param) {
                            $attrs = $param->getAttributes();
                            foreach ($attrs as $attr) {
                                if ($attr->getName() === 'Symfony\\Component\\HttpKernel\\Attribute\\MapRequestPayload') {
                                    $dtoClass = $param->getType()?->getName();
                                    if (class_exists($dtoClass)) {
                                        $dtoReflection = new \ReflectionClass($dtoClass);
                                        $fields = [];
                                        foreach ($dtoReflection->getProperties() as $prop) {
                                            $type = $prop->getType();
                                            if ($type) {
                                                $typeName = $type->getName();
                                                if ($type->allowsNull()) {
                                                    $fields[$prop->getName()] = 'null|' . $typeName;
                                                } else {
                                                    $fields[$prop->getName()] = $typeName;
                                                }
                                            } else {
                                                $fields[$prop->getName()] = 'mixed';
                                            }
                                        }
                                        $requestExample = $fields;
                                    }
                                }
                            }
                        }
                        // Response example: always string for success, error for error
                        $responseExample = [
                            'success' => 'Created',
                            'error' => [
                                'error' => 'UniqueConstraintViolation',
                                'code' => 400
                            ]
                        ];
                        $controllerInfo = [
                            'requestExample' => $requestExample,
                            'responseExample' => $responseExample
                        ];
                    }
                }
            }
            $routes[] = [
                'name' => $name,
                'path' => $route->getPath(),
                'methods' => $route->getMethods(),
                'controllerInfo' => $controllerInfo
            ];
        }

        ob_start();
        include __DIR__ . '/api-doc-template.php';
        $content = ob_get_clean();

        return new Response($content);
    }
}
