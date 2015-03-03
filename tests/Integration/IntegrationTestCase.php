<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Test\Integration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit_Framework_TestCase as TestCase;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;
use Zend\Mvc\Application;

class IntegrationTestCase extends TestCase
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
                                'params'      => [
                                    'memory' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->application = Application::init($this->config);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->application->getServiceManager()->get(EntityManager::class);
    }

    /**
     * @return DataMapperManager
     */
    public function getDataMapperManager()
    {
        return $this->application->getServiceManager()->get(DataMapperManager::class);
    }

    protected function initializeInMemoryDB()
    {
        $entityManager = $this->getEntityManager();

        // try a real schema creation with the in memory sqlite driver
        $classes    = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }
}
