<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Service\DriverFactory;
use Thorr\OAuth2\Entity;
use Thorr\Persistence\Doctrine\DataMapper as BaseDataMapper;
use Zend\ModuleManager\Feature;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

class Module implements Feature\ConfigProviderInterface
{
    /**
     * @param ModuleManager $moduleManager
     */
    public function init(ModuleManager $moduleManager)
    {
        $requiredModules = [
            'DoctrineModule',
            'DoctrineORMModule',
            'Thorr\Persistence',
            'Thorr\Persistence\Doctrine',
            'Thorr\OAuth2'
        ];

        foreach ($requiredModules as $module) {
            $moduleManager->loadModule($module);
        }
    }

    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application    = $event->getApplication();
        $serviceManager = $application->getServiceManager();

        /** @var Options\ModuleOptions $options */
        $options = $serviceManager->get(Options\ModuleOptions::class);

        // default User entity mapping is opt-out, as it will be overridden in most cases
        if ($options->isDefaultUserMappingEnabled()) {
            /** @var MappingDriverChain $doctrineDriverChain */
            $doctrineDriverChain = $serviceManager->get('doctrine.driver.orm_default');

            $driverFactory = new DriverFactory('thorr_oauth_optional_orm_xml_driver');
            $userMappingDriver = $driverFactory->createService($serviceManager);

            $doctrineDriverChain->addDriver($userMappingDriver, 'Thorr\OAuth2\Entity');
        }
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return [
            'thorr_oauth_doctrine' => [
                'default_user_mapping_enabled' => true,
                'user_credential_fields' => [ 'id' ],
            ],

            'thorr_persistence_dmm' => [
                'entity_data_mapper_map' => [
                    // key is the entity class, value is an arbitrary service name
                    Entity\AccessToken::class       => __NAMESPACE__ . '\AccessTokenDataMapper',
                    Entity\AuthorizationCode::class => __NAMESPACE__ . '\AuthCodeDataMapper',
                    Entity\Client::class            => __NAMESPACE__ . '\ClientDataMapper',
                    Entity\RefreshToken::class      => __NAMESPACE__ . '\RefreshTokenDataMapper',
                    Entity\Scope::class             => __NAMESPACE__ . '\ScopeDataMapper',
                    Entity\ThirdParty::class        => __NAMESPACE__ . '\ThirdPartyDataMapper',
                    Entity\User::class              => __NAMESPACE__ . '\UserDataMapper',
                ],
                'doctrine' => [
                    'object_manager' => EntityManager::class,
                    'adapters' => [
                        // key is the arbitrary service name, value is an adapter spec (string|array)
                        __NAMESPACE__ . '\AccessTokenDataMapper'  => DataMapper\TokenMapperAdapter::class,
                        __NAMESPACE__ . '\AuthCodeDataMapper'     => DataMapper\TokenMapperAdapter::class,
                        __NAMESPACE__ . '\ClientDataMapper'       => BaseDataMapper\DoctrineAdapter::class,
                        __NAMESPACE__ . '\RefreshTokenDataMapper' => DataMapper\TokenMapperAdapter::class,
                        __NAMESPACE__ . '\ScopeDataMapper'        => DataMapper\ScopeMapperAdapter::class,
                        __NAMESPACE__ . '\ThirdPartyDataMapper'   => BaseDataMapper\DoctrineAdapter::class,
                        __NAMESPACE__ . '\UserDataMapper'         => DataMapper\UserMapperAdapter::class,
                    ],
                ],
            ],

            'service_manager' => [
                'factories' => [
                    Options\ModuleOptions::class => Options\ModuleOptionsFactory::class,
                ],
                'delegators' => [
                    __NAMESPACE__ . '\UserDataMapper' => [
                        DataMapper\UserMapperAdapterInitializer::class,
                    ],
                ],
            ],

            'doctrine' => [
                'driver' => [
                    'thorr_oauth_orm_xml_driver' => [
                        'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                        'paths' => __DIR__ . '/mappings',
                    ],
                    'thorr_oauth_optional_orm_xml_driver' => [
                        'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                        'paths' => __DIR__ . '/mappings',
                        'extension' => '.dcm.optional.xml'
                    ],
                    'orm_default' =>[
                        'drivers' => [
                            Entity\AbstractToken::class     => 'thorr_oauth_orm_xml_driver',
                            Entity\AccessToken::class       => 'thorr_oauth_orm_xml_driver',
                            Entity\AuthorizationCode::class => 'thorr_oauth_orm_xml_driver',
                            Entity\Client::class            => 'thorr_oauth_orm_xml_driver',
                            Entity\RefreshToken::class      => 'thorr_oauth_orm_xml_driver',
                            Entity\Scope::class             => 'thorr_oauth_orm_xml_driver',
                            Entity\ThirdParty::class        => 'thorr_oauth_orm_xml_driver',
                        ]
                    ]
                ]
            ],
        ];
    }
}
