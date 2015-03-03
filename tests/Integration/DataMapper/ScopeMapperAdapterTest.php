<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Test\Integration\DataMapper;

use ArrayObject;
use Thorr\OAuth2\Doctrine\DataMapper\ScopeMapperAdapter;
use Thorr\OAuth2\Doctrine\Test\Integration\IntegrationTestCase;
use Thorr\OAuth2\Entity\Scope;
use Traversable;

class ScopeMapperAdapterTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initializeInMemoryDB();
    }

    /**
     * @param array|Traversable $args
     *
     * @dataProvider findScopesValidArgumentsProvider
     */
    public function testFindScopes($args)
    {
        $entityManager = $this->getEntityManager();

        $scope1 = new Scope(null, 'foo');
        $scope2 = new Scope(null, 'bar');
        $scope3 = new Scope(null, 'baz');

        $entityManager->persist($scope1);
        $entityManager->persist($scope2);
        $entityManager->persist($scope3);
        $entityManager->flush();

        /** @var ScopeMapperAdapter $scopeMapper */
        $scopeMapper = $this->getDataMapperManager()->getDataMapperForEntity(Scope::class);

        $result = $scopeMapper->findScopes($args);

        $this->assertContains($scope1, $result);
        $this->assertContains($scope2, $result);
        $this->assertNotContains($scope3, $result);
    }

    public function findScopesValidArgumentsProvider()
    {
        return [
            [ ['foo', 'bar'] ],
            [ new ArrayObject(['foo', 'bar']) ],
        ];
    }

    /**
     * @param mixed $arg
     * @dataProvider findScopesInvalidArgumentsProvider
     */
    public function testFindScopesThrowsExceptionIfArgumentIsNotArrayOrTraversable($arg)
    {
        /** @var ScopeMapperAdapter $scopeMapper */
        $scopeMapper = $this->getDataMapperManager()->getDataMapperForEntity(Scope::class);

        $this->setExpectedException('InvalidArgumentException', 'Argument must be an array or Traversable');
        $scopeMapper->findScopes($arg);
    }

    public function findScopesInvalidArgumentsProvider()
    {
        return [
            [ 'asd' ],
            [ new \stdClass() ],
        ];
    }

    public function testFindDefaultScopes()
    {
        $entityManager = $this->getEntityManager();

        $scope        = new Scope(null, 'foo', false);
        $defaultScope = new Scope(null, 'bar', true);

        $entityManager->persist($scope);
        $entityManager->persist($defaultScope);
        $entityManager->flush();

        /** @var ScopeMapperAdapter $scopeMapper */
        $scopeMapper = $this->getDataMapperManager()->getDataMapperForEntity(Scope::class);

        $result = $scopeMapper->findDefaultScopes();
        $this->assertContains($defaultScope, $result);
        $this->assertNotContains($scope, $result);
    }
}
