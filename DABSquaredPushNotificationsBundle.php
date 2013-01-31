<?php

namespace DABSquared\PushNotificationsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use DABSquared\PushNotificationsBundle\DependencyInjection\Compiler\AddHandlerPass;

class DABSquaredPushNotificationsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddHandlerPass());
    }
}
