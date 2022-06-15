<?php
// .phpstorm.meta.php

namespace PHPSTORM_META {

    use Ep\Base\Core;
    use Ep\Base\Contract\InjectorInterface;
    use Psr\EventDispatcher\EventDispatcherInterface;

    override(
        Core::ready(0),
        map([
            '' => '@'
        ])
    );

    override(
        InjectorInterface::make(0),
        map([
            '' => '@'
        ])
    );

    override(
        EventDispatcherInterface::dispatch(0),
        type(0)
    );
}
