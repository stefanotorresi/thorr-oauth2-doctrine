<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Thorr\OAuth2\DataMapper\UserMapperInterface;
use Thorr\OAuth2\Entity\UserInterface;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;
use Thorr\Persistence\Doctrine\ObjectManager\ObjectManagerGuardTrait;

/**
 * @method EntityManager getObjectManager()
 */
class UserMapperAdapter extends DoctrineAdapter implements UserMapperInterface
{
    use ObjectManagerGuardTrait;

    /**
     * @var array
     */
    protected $credentialFields = [ 'uuid' ];

    /**
     * {@inheritdoc}
     */
    public function __construct($entityClass, ObjectManager $objectManager)
    {
        $this->guardForSpecificObjectManager(EntityManager::class, $objectManager);

        parent::__construct($entityClass, $objectManager);
    }

    /**
     * @return array
     */
    public function getCredentialFields()
    {
        return $this->credentialFields;
    }

    /**
     * @param array $credentialFields
     */
    public function setCredentialFields($credentialFields)
    {
        $this->credentialFields = $credentialFields;
    }

    /**
     * @param  string             $credential may be any unique field value allowed as a login name
     * @return UserInterface|null
     */
    public function findByCredential($credential)
    {
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select('user')->from($this->entityClass, 'user');

        foreach ($this->getCredentialFields() as $field) {
            $queryBuilder->orWhere($queryBuilder->expr()->eq('user.'.$field, ':'.$field))
                ->setParameter($field, $credential);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
