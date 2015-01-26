<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Test\Integration\DataMapper;

use Doctrine\ORM\EntityManager;
use Thorr\OAuth2\Doctrine\DataMapper\ScopeMapperAdapter;
use Thorr\OAuth2\Doctrine\Test\Integration\IntegrationTestCase;
use Thorr\OAuth2\Entity\Scope;
use Thorr\Persistence\DataMapper\Manager\DataMapperManager;

class ScopeMapperAdapterTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initializeInMemoryDB();
    }

    public function testFindDefaultScope()
    {
        $serviceManager = $this->application->getServiceManager();

        $entityManager = $this->getEntityManager();

        $scope = new Scope(null, 'foo', false);
        $defaultScope = new Scope(null, 'bar', true);

        $entityManager->persist($scope);
        $entityManager->persist($defaultScope);
        $entityManager->flush();

        /** @var DataMapperManager $dataMapperManager */
        $dataMapperManager = $serviceManager->get(DataMapperManager::class);

        /** @var ScopeMapperAdapter $scopeMapper */
        $scopeMapper = $dataMapperManager->getDataMapperForEntity(Scope::class);

        $defaultScopes = $scopeMapper->findDefaultScopes();
        $this->assertContains($defaultScope, $defaultScopes);
        $this->assertNotContains($scope, $defaultScopes);
    }
}
