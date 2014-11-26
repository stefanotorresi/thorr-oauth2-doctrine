<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Test\Integration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\OAuth2\Doctrine\Module;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Zend\Mvc\Application;

class ModuleTest extends TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var
     */
    protected $config;

    /**
     *
     */
    public function setUp()
    {
        $this->config = [
            'modules' => [
                'Thorr\OAuth2\Doctrine',
            ],
            'module_listener_options' => [
                'extra_config' => [
                    'doctrine' => [
                        'connection' => [
                            'orm_default' => [
                                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                                'params' => [
                                    'memory' => true,
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ];

        $this->application = $application = Application::init($this->config);
    }

    public function testModuleLoading()
    {
        $moduleManager = $this->application->getServiceManager()->get('ModuleManager');

        $this->assertContains('Thorr\\OAuth2\\Doctrine', $moduleManager->getModules());
        $this->assertInstanceOf(Module::class, $moduleManager->getModule('Thorr\\OAuth2\\Doctrine'));
    }

    public function testEntityMappings()
    {
        $serviceManager = $this->application->getServiceManager();

        /** @var EntityManager $entityManager */
        $entityManager = $serviceManager->get(EntityManager::class);

        $entities = array_keys($serviceManager->get('config')['thorr_persistence_dmm']['entity_data_mapper_map']);

        foreach ($entities as $entity) {
            $this->assertInstanceOf(ClassMetadata::class, $entityManager->getClassMetadata($entity));
        }

        // try a real schema creation with the in memory sqlite driver
        $classes    = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
        $this->assertTrue($entityManager->getConnection()->isConnected());
    }

    public function testDataMappers()
    {
        $serviceManager = $this->application->getServiceManager();

        /** @var DataMapperManager $dataMapperManager */
        $dataMapperManager = $serviceManager->get(DataMapperManager::class);

        $dataMappers = $serviceManager->get('config')['thorr_persistence_dmm']['entity_data_mapper_map'];

        foreach ($dataMappers as $entityClass => $dataMapperService) {
            /** @var DoctrineAdapter $dataMapper */
            $dataMapper = $dataMapperManager->getDataMapperForEntity($entityClass);
            $this->assertInstanceOf(DoctrineAdapter::class, $dataMapper);
            $this->assertSame($entityClass, $dataMapper->getEntityClass());
        }
    }
}
