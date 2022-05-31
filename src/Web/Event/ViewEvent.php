<?php

declare(strict_types=1);

namespace Ep\Web\Event;

use Ep\Web\View;

abstract class ViewEvent
{
    public function __construct(private View $view)
    {
    }

    public function getView(): View
    {
        return $this->view;
    }
}
