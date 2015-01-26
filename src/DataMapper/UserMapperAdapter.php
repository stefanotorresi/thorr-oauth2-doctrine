<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\DataMapper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use RuntimeException;
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
        $rootAlias = 'user';
        $queryBuilder->select($rootAlias)->from($this->entityClass, $rootAlias);

        foreach ($this->getCredentialFields() as $field) {
            $whereAlias = $rootAlias;

            // if a credential field contains a dot, we need to add a LEFT JOIN statement
            if (strpos($field, '.') !== false) {
                $joinField = explode('.', $field);
                if (count($joinField) != 2) {
                    throw new RuntimeException(sprintf("Invalid credential join field '%s'", $field));
                }
                $whereAlias = $joinField[0];
                $field = $joinField[1];
                $queryBuilder->leftJoin($rootAlias.'.'.$whereAlias, $whereAlias);
            }

            $queryBuilder->orWhere($queryBuilder->expr()->eq($whereAlias.'.'.$field, ':'.$field))
                         ->setParameter($field, $credential);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
