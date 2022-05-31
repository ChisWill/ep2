<?php

declare(strict_types=1);

namespace Ep\Tests\App\Filter;

use Ep;
use Ep\Contract\FilterInterface;
use Ep\Contract\FilterTrait;

class RootFilter implements FilterInterface
{
    use FilterTrait;

    public function __construct()
    {
        $this->setMiddlewares([]);
    }

    public function before($request)
    {
        // t('root start');
        // return Ep::getDi()->get(Service::class)->string('over');
        return true;
    }

    public function after($request, $response)
    {
        // t('root over');

        return $response;
    }
}
