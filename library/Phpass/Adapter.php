<?php
/**
 * Portable PHP password hashing framework.
 *
 * @package PHPass
 * @subpackage Adapters
 * @category Cryptography
 * @author Solar Designer <solar at openwall.com>
 * @author Ryan Chouinard <rchouinard at gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link http://www.openwall.com/phpass/ Original phpass project page.
 * @link https://github.com/rchouinard/phpass PHPass project at GitHub.
 */

/**
 * @namespace
 */
namespace Phpass;

/**
 * Portable PHP password hashing framework.
 *
 * @package PHPass
 * @subpackage Adapters
 * @category Cryptography
 * @author Solar Designer <solar at openwall.com>
 * @author Ryan Chouinard <rchouinard at gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link http://www.openwall.com/phpass/ Original phpass project page.
 * @link https://github.com/rchouinard/phpass PHPass project at GitHub.
 */
interface Adapter
{

    /**
     * Generate a hash based on the password string.
     *
     * @param string $password
     *   The plain-text password string.
     * @param string $salt
     *   Optional; The salt or stored hash value used to generate a new hash.
     * @return string
     *   Hashed version of the password string.
     */
    public function crypt($password, $salt = null);

    /**
     * Generate a salt string suitable for use with the adapter.
     *
     * @param string $input
     *   Optional; Random data used to generate the salt.
     * @return string
     *   Generated salt string.
     */
    public function genSalt($input = null);


    /**
     * Check if the adapter is supported on the system.
     *
     * @return boolean
     *   True if the system has all the required dependencies to use the
     *   adapter, false otherwise.
     */
    public function isSupported();

    /**
     * Check if a hash is compatible with the adapter.
     *
     * @param string $hash
     *   Password hash string.
     * @return boolean
     *   True if the hash is compatible with the adapter, false otherwise.
     */
    public function isValid($hash);

}