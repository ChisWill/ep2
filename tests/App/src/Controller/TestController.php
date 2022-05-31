<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Service\TestService;
use Psr\Http\Message\ServerRequestInterface;
use Ep\Annotation\Inject;
use Ep\Annotation\Route;
use Ep\Contract\InjectorInterface;
use Ep\Tests\App\Aspect\ClassAnnotation;
use Ep\Tests\App\Service\DemoService;
use Yiisoft\Db\Connection\Connection;

/**
 * @ClassAnnotation
 * @Route(value="t")
 */
class TestController extends Controller
{
    /**
     * @Inject(name="mary")
     */
    private TestService $service;
    /**
     * @Inject
     */
    private DemoService $demoService;

    /**
     * @Inject
     */
    private InjectorInterface $injector;

    private Connection $db;

    public function __construct()
    {
        $this->setMiddlewares([]);

        $this->db = Ep::getDb('sqlite');
    }

    /**
     * @Route("index", {"GET","POST"})
     */
    public function indexAction(ServerRequestInterface $serverRequest)
    {
        $view = $this->getView()->withLayout('test');

        $message = 'hi';

        return $this->string($view->render('/index/index', compact('message')));
    }
}
