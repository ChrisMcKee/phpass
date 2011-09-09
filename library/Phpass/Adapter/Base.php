<?php
/**
 * Portable PHP password hashing framework.
 *
 * @package PHPass
 * @subpackage Adapters
 * @category Cryptography
 * @author Solar Designer <solar at openwall.com>
 * @author Ryan Chouinard <rchouinard at gmail.com>
 * @license Public Domain
 * @link http://www.openwall.com/phpass/ Original phpass project page.
 * @version 0.4
 */

/**
 * @namespace
 */
namespace Phpass\Adapter;

/**
 * @see \Phpass\Adapter
 */
require_once 'Phpass/Adapter.php';

/**
 * Portable PHP password hashing framework.
 *
 * @package PHPass
 * @subpackage Adapters
 * @category Cryptography
 * @author Solar Designer <solar at openwall.com>
 * @author Ryan Chouinard <rchouinard at gmail.com>
 * @license Public Domain
 * @link http://www.openwall.com/phpass/ Original phpass project page.
 * @version 0.4
 */
abstract class Base implements \Phpass\Adapter
{

    /**
     * @var integer
     */
    protected $_iterationCountLog2;

    /**
     * @var string
     */
    protected $_itoa64;

    /**
     * @var string
     */
    protected $_randomState;


    /**
     * @param array $options
     * @return void
     */
    public function __construct(Array $options = array ())
    {
        $this->_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $this->_iterationCountLog2 = 8;

        $this->_randomState = microtime();
        if (function_exists('getmypid')) {
            $this->_randomState .= getmypid();
        }

        $this->setOptions($options);
    }

    /**
     * (non-PHPdoc)
     * @see Phpass_AdapterInterface::crypt()
     */
    public function crypt($password, $salt = null)
    {
        if (!$salt) {
            $salt = $this->genSalt();
        }
        return crypt($password, $salt);
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(Array $options)
    {
        $options = array_change_key_case($options, CASE_LOWER);

        foreach ($options as $key => $value) {
            switch ($key) {

                case 'iterationcountlog2':
                    $this->_iterationCountLog2 = (int) $value;
                    break;

                default:
                    break;

            }
        }
    }

    /**
     * @param string $input
     * @param integer $count
     */
    protected function _encode64($input, $count)
    {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->_itoa64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $output .= $this->_itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $output .= $this->_itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $output .= $this->_itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    /**
     * @param integer $count
     * @return string
     */
    protected function _getRandomBytes($count)
    {
        $output = '';
        if (is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
            $output = fread($fh, $count);
            fclose($fh);
        }

        if (strlen($output) < $count) {
            $output = '';
            for ($i = 0; $i < $count; $i += 16) {
                $this->_randomState = md5(microtime() . $this->_randomState);
                $output .= md5($this->_randomState, true);
            }
            $output = substr($output, 0, $count);
        }

        return $output;
    }

    /**
     * @param string $adapter
     * @param array $options
     * @return Phpass_Adapter
     */
    static public function factory($adapter, Array $options = array ())
    {
        if (!is_string($adapter)) {
            throw new \InvalidArgumentException(
                'Required argument $adapter is expected to be a string ' .
                'containing the name of an adapter, or a salt value.'
            );
        }

        // Generic adapter names
        if (strtolower($adapter) == 'blowfish' || substr($adapter, 0, 4) == '$2a$') {
            $adapter = '\Phpass\Adapter\Blowfish';
        } else if (strtolower($adapter) == 'extdes' || substr($adapter, 0, 1) == '_') {
            $adapter = '\Phpass\Adapter\ExtDes';
        } else if (strtolower($adapter) == 'portable' || substr($adapter, 0, 3) == '$P$' || substr($adapter, 0, 3) == '$H$') {
            $adapter = '\Phpass\Adapter\Portable';
        }

        $adapter = ucfirst($adapter);

        // Assume $adapter is a class name mappable to a file.
        if (!class_exists($adapter, false)) {
            $file = trim(str_replace(array ('\\', '_'), DIRECTORY_SEPARATOR, $adapter), DIRECTORY_SEPARATOR);
            if (file_exists($file)) {
                @include $file;
            }
        }

        if (class_exists($adapter, false)) {
            $class = $adapter;
            $instance = new $class;
            if ($instance instanceof \Phpass\Adapter) {
                $instance = new $class($options);
                return $instance;
            }
            unset ($instance);
        }

        if (substr($adapter, 0, 15) != '\\Phpass\\Adapter') {
            $adapter = '\\Phpass\\Adapter\\' . $adapter;
            $instance = self::factory($adapter, $options);
            if ($instance instanceof \Phpass\Adapter) {
                return $instance;
            }
        }

        throw new \Exception(
            "Failed loading adapter '${adapter}'"
        );
    }

}