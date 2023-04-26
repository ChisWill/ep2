<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Base\RouteCollection;
use Ep\Kit\UrlGenerator;
use Ep\Tests\App\Annotation\MethodAttribute;
use Ep\Tests\App\Annotation\TestAspect1;
use Ep\Tests\App\Annotation\TestAspect2;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Objects\Human\Child;
use Ep\Tests\App\Service\TestService;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use ReflectionMethod;

#[Route('t')]
class TestController extends Controller
{
    private ConnectionInterface $db;

    public function __construct(
        private TestService $testService
    ) {
        $this->db = Ep::getDb('sqlite');
    }

    public function index(ServerRequestInterface $request)
    {
        $this->testService->index();

        return $this->success();
    }

    public function view()
    {
        $message = 'Title';

        return $this->render('/index/index', compact('message'));
    }

    #[Inject(name: 'lala')]
    private Child $child;

    #[MethodAttribute('name', age: 10, params: ['key' => 'value'])]
    public function attribute(Child $child)
    {
        tt($this->child->do(), $child->do());

        $method = new ReflectionMethod($this, 'attributeAction');
        foreach ($method->getAttributes(MethodAttribute::class) as $v) {
            tt($v->newInstance());
        }
    }

    #[TestAspect1(name: 'first'), TestAspect2(name: 'second')]
    public function aspect()
    {
        t($this->father->getName());
        return $this->success();
    }

    public function route(RouteCollection $routeCollection, UrlGenerator $urlGenerator)
    {
        // tt($routeCollection->getRoutes(), $routeCollection->getNames());
        return $this->string(
            $urlGenerator->generate('t-parent', [
                'ctrl' => 'test',
                'action' => 'route',
                'id' => 9
            ])
        );
    }
}
