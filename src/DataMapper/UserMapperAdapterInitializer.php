<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\DataMapper;

use Thorr\OAuth2\Doctrine\Options\ModuleOptions;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserMapperAdapterInitializer implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $serviceManager = $serviceLocator instanceof AbstractPluginManager ? $serviceLocator->getServiceLocator()
            : $serviceLocator;

        /** @var UserMapperAdapter $adapter */
        $adapter = $callback();

        /** @var ModuleOptions $options */
        $options = $serviceManager->get(ModuleOptions::class);

        $adapter->setCredentialFields($options->getUserCredentialFields());

        return $adapter;
    }
}
