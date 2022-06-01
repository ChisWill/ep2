<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Attribute\Route;
use Ep\Tests\App\Component\Controller;

class StateController extends Controller
{
    #[Route('ping')]
    public function pingAction()
    {
        return $this->string('pong');
    }

    #[Route('post', 'POST')]
    public function postAction()
    {
        return $this->string('post');
    }
}
