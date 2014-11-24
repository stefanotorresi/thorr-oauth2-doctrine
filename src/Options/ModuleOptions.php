<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\OAuth2\Doctrine\Options;

use Thorr\OAuth2\Options\ModuleOptions as BaseModuleOptions;
use Zend\Stdlib\ArrayUtils;

class ModuleOptions extends BaseModuleOptions
{
    /**
     * @var bool
     */
    protected $defaultUserMappingEnabled = true;

    /**
     * @var array
     */
    protected $userCredentialFields = [ 'id' ];

    /**
     * @return boolean
     */
    public function isDefaultUserMappingEnabled()
    {
        return $this->defaultUserMappingEnabled;
    }

    /**
     * @param boolean $loadDefaultUserMapping
     */
    public function setDefaultUserMappingEnabled($loadDefaultUserMapping)
    {
        $this->defaultUserMappingEnabled = (bool) $loadDefaultUserMapping;
    }

    /**
     * @return array
     */
    public function getUserCredentialFields()
    {
        return $this->userCredentialFields;
    }

    /**
     * @param array $userCredentialFields
     */
    public function setUserCredentialFields($userCredentialFields)
    {
        $this->userCredentialFields = $userCredentialFields;
    }
}
