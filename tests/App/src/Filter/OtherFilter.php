<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep\Contract\FilterInterface;
use Ep\Contract\FilterTrait;

class OtherFilter implements FilterInterface
{
    use FilterTrait;

    public function __construct()
    {
        $this->setMiddlewares([]);
    }

    public function before($request)
    {
        // t('other start');
        return true;
    }

    public function after($request, $response)
    {
        // t('other over');

        return $response;
    }
}
