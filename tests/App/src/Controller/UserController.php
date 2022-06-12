<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Auth\AuthRepository;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Model\Student;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\Middleware\Authentication;

class UserController extends Controller
{
    public function __construct(private AuthRepository $authRepository)
    {
    }

    public function info(ServerRequestInterface $request)
    {
        /** @var Student */
        $student = $request->getAttribute(Authentication::class);

        return $this->json([
            'id' => $student->getId(),
            'all' => $student->getAttributes()
        ]);
    }
}
