<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Thorr\OAuth2\DataMapper\ScopeMapperInterface;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerGuardTrait;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;

/**
 * @method EntityManager getObjectManager()
 */
class ScopeMapperAdapter extends DoctrineAdapter implements ScopeMapperInterface
{
    use ArrayOrTraversableGuardTrait;
    use ObjectManagerGuardTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($entityClass, ObjectManager $objectManager)
    {
        $this->guardForSpecificObjectManager(EntityManager::class, $objectManager);

        parent::__construct($entityClass, $objectManager);
    }


    /**
     * @param $scopes
     * @return array|Traversable
     */
    public function findScopes($scopes)
    {
        $this->guardForArrayOrTraversable($scopes);

        if (! is_array($scopes)) {
            $scopes = ArrayUtils::iteratorToArray($scopes);
        }

        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select()
            ->from($this->entityClass, 'scope')
            ->where($queryBuilder->expr()->in('scope.name', $scopes))
        ;

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return array
     */
    public function findDefaultScopes()
    {
        return $this->getObjectManager()->getRepository($this->entityClass)->findBy(['default' => true]);
    }
}
