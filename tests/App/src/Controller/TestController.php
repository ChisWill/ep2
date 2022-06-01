<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Attribute\Inject;
use Ep\Attribute\Route;
use Ep\Tests\App\Annotation\MethodAttribute;
use Ep\Tests\App\Annotation\TestAspect1;
use Ep\Tests\App\Annotation\TestAspect2;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Objects\Human\Child;
use Ep\Tests\App\Service\TestService;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use Yiisoft\Db\Connection\Connection;

#[Route('t', method: 'GET')]
class TestController extends Controller
{
    private Connection $db;

    public function __construct(
        private TestService $testService
    ) {
        $this->setMiddlewares([]);

        $this->db = Ep::getDb('sqlite');
    }

    public function indexAction(ServerRequestInterface $serverRequest)
    {
        $this->testService->index();
    }

    public function viewAction()
    {
        $message = 'Title';

        return $this->render('/index/index', compact('message'));
    }

    #[Inject(name: 'lala')]
    private Child $child;

    #[MethodAttribute('name', age: 10, params: ['key' => 'value'])]
    public function attributeAction(Child $child)
    {
        tt($this->child->do(), $child->do());

        $method = new ReflectionMethod($this, 'attributeAction');
        foreach ($method->getAttributes(MethodAttribute::class) as $v) {
            tt($v->newInstance());
        }
    }

    #[TestAspect1(name: 'first'), TestAspect2(name: 'second')]
    public function aspectAction()
    {
    }
}
