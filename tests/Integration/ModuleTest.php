<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Test\Integration;

use Doctrine\ORM\Mapping\ClassMetadata;
use Thorr\OAuth2\Doctrine\Module;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;

class ModuleTest extends IntegrationTestCase
{
    public function testModuleLoading()
    {
        $moduleManager = $this->application->getServiceManager()->get('ModuleManager');

        $this->assertContains('Thorr\\OAuth2\\Doctrine', $moduleManager->getModules());
        $this->assertInstanceOf(Module::class, $moduleManager->getModule('Thorr\\OAuth2\\Doctrine'));
    }

    public function testEntityMappings()
    {
        $serviceManager = $this->application->getServiceManager();

        $entityManager = $this->getEntityManager();

        $entities = array_keys($serviceManager->get('config')['thorr_persistence_dmm']['entity_data_mapper_map']);

        foreach ($entities as $entity) {
            $this->assertInstanceOf(ClassMetadata::class, $entityManager->getClassMetadata($entity));
        }

        $this->initializeInMemoryDB();

        $this->assertTrue($entityManager->getConnection()->isConnected());
    }

    public function testDataMappers()
    {
        $dataMapperManager = $this->getDataMapperManager();
        $serviceManager    = $this->application->getServiceManager();
        $dataMappers       = $serviceManager->get('config')['thorr_persistence_dmm']['entity_data_mapper_map'];

        foreach ($dataMappers as $entityClass => $dataMapperService) {
            /** @var DoctrineAdapter $dataMapper */
            $dataMapper = $dataMapperManager->getDataMapperForEntity($entityClass);
            $this->assertInstanceOf(DoctrineAdapter::class, $dataMapper);
            $this->assertSame($entityClass, $dataMapper->getEntityClass());
        }
    }
}
