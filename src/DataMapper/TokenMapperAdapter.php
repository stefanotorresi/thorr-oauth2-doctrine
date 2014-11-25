<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\DataMapper;

use Thorr\OAuth2\DataMapper\TokenMapperInterface;
use Thorr\OAuth2\Entity\AbstractToken;
use Thorr\Persistence\Doctrine\DataMapper\DoctrineAdapter;

class TokenMapperAdapter extends DoctrineAdapter implements TokenMapperInterface
{
    /**
     * @param  string             $token
     * @return AbstractToken|null
     */
    public function findByToken($token)
    {
        return $this->getObjectManager()->getRepository($this->entityClass)->findOneBy(['token' => $token]);
    }
}
