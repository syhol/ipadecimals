<?php
namespace SyHolloway\IpaDecimals;

use Exception;
use Math_BigInteger as BigInteger;

/**
 * IpAddress class to convert Human readable Ip address into a decimal format
 *
 * IpAddress::decimalToIp and IpAddress::ipToDecimal are adapted from 
 * Sam Clarkes functions at the link below.
 * 
 * @link http://www.samclarke.com/2011/07/php-ipv6-to-128bit-int/
 * @author Simon Holloway <holloway.sy@gmail.com>
 */
class IpAddress {

    /**
     * Human readable string of the Ip address
     * 
     * @var string
     */
    protected $ip;

    /**
     * Ip address stored in the decimal format
     * 
     * (but with the string datatype becuase damn it can get big)
     * 
     * @var string
     */
    protected $decimal;

    /**
     * Version of the Ip Protocol used
     * 
     * @var integer
     */
    protected $version;

    /**
     * Construct the IpAddress with an ip, decimal and a version
     *
     * @throws NotValidIpException If invalid Ip
     * @param string $ip Human readable Ip address
     */
    public function __construct($ip)
    {
        if ( ! static::isValidIp($ip) )
        {
            throw new NotValidIpException($ip);
        }

        $this->decimal = static::ipToDecimal($ip);

        $this->ip = static::decimalToIp($this->decimal);

        $this->version = static::getVersionFromIp($this->ip);
    }

    /**
     * Return the Human readable Ip address stored in the object
     *  
     * @return integer
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Return the Ip address in a decimal format stored in the object
     *  
     * @return string
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * Return the Ip version in the object
     *  
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Check the version in the object against a passed version
     * 
     * @param  integer $check   version number to check against
     * @return boolean          does the version number matched the passed integer
     */
    public function checkVersion($check)
    {
        return $this->version === (integer)$check;
    }

    /**
     * Instantiate this object using a human readable Ip address 
     * 
     * @param  string $ip human readable Ip address
     * @return IpAddress
     */
    public static function fromIp($ip)
    {
        return new static($ip);
    }

    /**
     * Instantiate this object using an IP in decimal format 
     * 
     * @param  string $decimal IP as decimal
     * @return IpAddress
     */
    public static function fromDecimal($decimal)
    {
        $ip = static::decimalToIp($decimal);
        return new static($ip);
    }

    /**
     * Check if the passed IP address is a valid ipv4 or ipv6 address
     * 
     * @param  string  $ip human readable Ip address
     * @return boolean
     */
    protected static function isValidIp($ip)
    {
        return static::getVersionFromIp($ip) !== false;
    }

    /**
     * Get the ip version from a passed Ip
     * 
     * @param  string  $ip human readable Ip address
     * @return integer|false
     */
    protected static function getVersionFromIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            return 4;
        }
        elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            return 6;
        }
        else
        {
            return false;
        }
    }

    /**
     * Converts human readable representation to a 128bit int
     * which can be stored in MySQL using DECIMAL(39,0).
     *
     * Requires PHP to be compiled with IPv6 support.
     * This could be made to work without IPv6 support but
     * I don't think there would be much use for it if PHP
     * doesn't support IPv6.
     *
     * @author Sam Clarke
     * @link http://www.samclarke.com/2011/07/php-ipv6-to-128bit-int/
     * @param string $ip IPv4 or IPv6 address to convert
     * @return string 128bit string that can be used with DECIMNAL(39,0) or false
     */
    protected static function ipToDecimal($ip)
    {
        // make sure it is an ip
        if (filter_var($ip, FILTER_VALIDATE_IP) === false)
            throw new NotValidIpException($ip);

        $parts = unpack('N*', inet_pton($ip));

        // fix IPv4
        if (strpos($ip, '.') !== false)
            $parts = array(1 => 0, 2 => 0, 3 => 0, 4 => $parts[1]);

        foreach ($parts as &$part)
        {
            // convert any unsigned ints to signed from unpack.
            // this should be OK as it will be a PHP float not an int
            if ($part < 0)
                $part  = 4294967296;
        }

        $decimal = new BigInteger($parts[4]);
        $part3   = new BigInteger($parts[3]);
        $part2   = new BigInteger($parts[2]);
        $part1   = new BigInteger($parts[1]);

        $decimal = $decimal->add($part3->multiply(new BigInteger('4294967296')));
        $decimal = $decimal->add($part2->multiply(new BigInteger('18446744073709551616')));
        $decimal = $decimal->add($part1->multiply(new BigInteger('79228162514264337593543950336')));

        $decimal = $decimal->toString();

        return $decimal;
    }

    /**
     * Converts a 128bit int to a human readable representation.
     *
     * Requires PHP to be compiled with IPv6 support.
     * This could be made to work without IPv6 support but
     * I don't think there would be much use for it if PHP
     * doesn't support IPv6.
     *
     * @author Sam Clarke
     * @link http://www.samclarke.com/2011/07/php-ipv6-to-128bit-int/
     * @param string $decimal 128bit int
     * @return string IPv4 or IPv6
     */
    protected static function decimalToIp($decimal)
    {
        $parts = array();

        $decimal = new BigInteger($decimal);
        list($parts[1],) = $decimal->divide(new BigInteger('79228162514264337593543950336'));
        $decimal = $decimal->subtract($parts[1]->multiply(new BigInteger('79228162514264337593543950336')));
        list($parts[2],) = $decimal->divide(new BigInteger('18446744073709551616'));
        $decimal = $decimal->subtract($parts[2]->multiply(new BigInteger('18446744073709551616')));
        list($parts[3],) = $decimal->divide(new BigInteger('4294967296'));
        $decimal = $decimal->subtract($parts[3]->multiply(new BigInteger('4294967296')));
        $parts[4] = $decimal;

        $parts[1] = $parts[1]->toString();
        $parts[2] = $parts[2]->toString();
        $parts[3] = $parts[3]->toString();
        $parts[4] = $parts[4]->toString();

        foreach ($parts as &$part)
        {
            // convert any signed ints to unsigned for pack
            // this should be fine as it will be treated as a float
            if ($part > 2147483647)
                $part -= 4294967296;
        }

        $ip = inet_ntop(pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]));

        // fix IPv4 by removing :: from the beginning
        if (strpos($ip, '.') !== false)
            return substr($ip, 2);

        return $ip;
    }
}

/**
 * Handle bad IPs
 *
 * Handle bad IPs by instantiating with and IP and augmenting the message to 
 * make it look more like a proper exception message
 * 
 * @author Simon Holloway <holloway.sy@gmail.com>
 */
class NotValidIpException extends Exception {

    /**
     * Augment the message to make it look more like a proper exception message
     * 
     * @param string $ip Human readable Ip
     */
    public function __construct($ip)
    {
        parent::__construct(sprintf('The string "%s" is not a valid IP Address', $ip));
    }
}
