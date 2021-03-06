<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\User;

use Simplerenew\Api\Plan;
use Simplerenew\Exception;
use Simplerenew\Object;

defined('_JEXEC') or die();

/**
 * Class User
 *
 * @package Simplerenew
 *
 */
class User extends Object
{
    /**
     * @var int
     */
    public $id = null;

    /**
     * @var string
     */
    public $username = null;

    /**
     * @var string
     */
    public $email = null;

    /**
     * @var string
     */
    public $password = null;

    /**
     * @var String
     */
    public $password2 = null;

    /**
     * @var string
     */
    public $firstname = null;

    /**
     * @var string
     */
    public $lastname = null;

    /**
     * @var array
     */
    public $groups = array();

    /**
     * @var bool
     */
    public $enabled = null;

    /**
     * @var Adapter\UserInterface
     */
    protected $adapter = null;

    /**
     * @var int
     */
    protected $expirationGroup = null;

    public function __construct(Adapter\UserInterface $adapter, array $config = array())
    {
        $this->adapter = $adapter;

        if (empty($config['expirationGroup'])) {
            throw new Exception('Configuration problem - Expiration user group is required');
        }
        $this->expirationGroup = $config['expirationGroup'];
    }

    /**
     * Load a user from system ID
     *
     * @param int $id
     *
     * @return User
     * @throws Exception
     */
    public function load($id = null)
    {
        $this->clearProperties();
        $this->id = $id;
        $this->adapter->load($this);

        return $this;
    }

    /**
     * Load a user from their username
     *
     * @param $username
     *
     * @return User
     * @throws Exception
     */
    public function loadByUsername($username)
    {
        $this->username = $username;
        $this->adapter->loadByUsername($this);

        return $this;
    }

    /**
     * Load a user from their email address
     *
     * @param $email
     *
     * @return User
     * @throws Exception
     */
    public function loadByEmail($email)
    {
        $this->email = $email;
        $this->adapter->loadByEmail($this);

        return $this;
    }

    public function asString()
    {
        return get_class($this->adapter);
    }

    /**
     * Create a new user.
     *
     * @return User
     * @throws Exception
     */
    public function create()
    {
        if (empty($this->id)) {
            $this->adapter->create($this);

            return $this;
        }

        throw new Exception('User ID must not be set to create new user');
    }

    /**
     * Update the current user with the current property settings
     *
     * @return User
     * @throws Exception
     */
    public function update()
    {
        if (!empty($this->id)) {
            $this->adapter->update($this);

            return $this;
        }

        throw new Exception('No current user to update.');
    }

    /**
     * Check the password for validity
     *
     * @param $password
     *
     * @return bool
     */
    public function validate($password)
    {
        return $this->adapter->validate($this, $password);
    }

    /**
     * Login the user
     *
     * @param string $password
     * @param bool   $force
     *
     * @return void
     * @throws Exception
     */
    public function login($password, $force = false)
    {
        $this->adapter->login($this, $password, $force);
    }

    /**
     * Logout the user
     *
     * @return void
     * @throws Exception
     */
    public function logout()
    {
        $this->adapter->logout($this);
    }

    /**
     * Add user groups based on selected plans
     *
     * @param mixed $planCodes Plan code or array of plan codes to add to user
     * @param bool  $replace   Clear all subscriber groups
     *
     * @return User
     * @throws Exception
     */
    public function addGroups($planCodes, $replace = false)
    {
        $this->adapter->addGroups($this, (array)$planCodes, $replace);
        return $this;
    }

    /**
     * @param mixed $planCodes Plan code or array of plan codes
     *
     * @return User
     * @throws Exception
     */
    public function removeGroups($planCodes)
    {
        $this->adapter->removeGroups($this, (array)$planCodes);
        return $this;
    }

    /**
     * Get a human friendly version of group membership
     *
     * @return string
     */
    public function getGroupText()
    {
        return $this->adapter->getGroupText($this);
    }

    /**
     * Returns the user group used when a subscription expires
     */
    public function getExpirationGroup()
    {
        return $this->expirationGroup;
    }
}
