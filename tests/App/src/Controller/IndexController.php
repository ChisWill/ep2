<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Tests\App\Component\Controller;

class IndexController extends Controller
{
    public string $title = '首页';

    public function index()
    {
        $message = 'Default Page';

        return $this->render('index', compact('message'));
    }
}
