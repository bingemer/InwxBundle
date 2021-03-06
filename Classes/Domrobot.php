<?php
/**
     * Created by PhpStorm.
     * User: joerg
     * Date: 14.02.16
     * Time: 20:31
     * copied and modified from https://github.com/inwx/php-client
     */
namespace Bingemer\InwxBundle\Classes;

/**
 * Class Domrobot
 * @package Bingemer\InwxBundle\Classes
 */
class Domrobot
{
    private $debug = false;
    private $address;
    private $language;
    private $customer = "";
    private $clTRID = null;

    private $_ver = "2.4";
    private $_cachedir;
    private $_cookiefile = NULL;
    private $loginResult = NULL;

    /**
     * Domrobot constructor.
     * @param $address
     * @param string $locale
     * @param $cache_dir
     * @param $username
     * @param $password
     * @param null $sharedSecret
     */
    public function __construct($address, $locale = 'en', $cache_dir, $username, $password, $sharedSecret = null)
    {
        $this->address = (substr($address, -1) != "/") ? $address . "/" : $address;
        $this->_cachedir = $cache_dir;
        $this->_cookiefile = tempnam($this->_cachedir, 'INWX');
        $this->setLanguage($locale);
        $this->loginResult = $this->login($username, $password, $sharedSecret);
    }

    /**
     * @param string $name
     * @param $ip
     * @param string $domain
     * @param string $type
     * @param int $ttl
     * @return mixed|string
     */
    public function createRecord($name, $ip, $domain = 'somedomainnameyouwishtoupgrade.de', $type = 'A', $ttl = 3600)
    {
        if (strpos($name, $domain) === false) {
            $name = $name . "." . $domain;
        }
        try {
            $result = $this->call('nameserver', 'createRecord', array(
                'domain' => $domain,
                'type' => $type,
                'name' => $name,
                'content' => $ip,
                'ttl' => $ttl
            ));
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param integer $id
     * @param $ip
     * @return mixed
     */
    public function updateRecord($id, $ip)
    {
        $result = $this->call('nameserver', 'updateRecord', array(
            'id' => $id,
            'content' => $ip
        ));
        return $result;
    }

    /**
     * @param integer $id
     * @param bool $testing
     * @return mixed
     */
    public function deleteRecord($id, $testing = false)
    {
        $result = $this->call('nameserver', 'deleteRecord', array(
            'id' => $id,
            'testing' => $testing
        ));
        return $result;
    }

    /**
     * @param $username
     * @param $password
     * @param null $sharedSecret
     * @return mixed
     */
    public function login($username, $password, $sharedSecret = null)
    {
        $fp = fopen($this->_cookiefile, "w");
        fclose($fp);

        $params = array();
        if (!empty($this->language)) {
            $params['lang'] = $this->language;
        } else {
            $params['lang'] = "en"; //fallback
        }
        $params['user'] = $username;
        $params['pass'] = $password;

        $loginRes = $this->call('account', 'login', $params);
        if (!empty($sharedSecret) && $loginRes['code'] == 1000 && !empty($loginRes['resData']['tfa'])) {
            $_tan = $this->_getSecretCode($sharedSecret);
            $unlockRes = $this->call('account', 'unlock', array('tan' => $_tan));
            if ($unlockRes['code'] == 1000) {
                return $loginRes;
            } else {
                return $unlockRes;
            }
        } else {
            return $loginRes;
        }
    }

    /**
     * @param string $object
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call($object, $method, array $params = array())
    {
        if (isset($this->customer) && $this->customer != "") {
            $params['subuser'] = $this->customer;
        }
        if (!empty($this->clTRID)) {
            $params['clTRID'] = $this->clTRID;
        }

        $request = xmlrpc_encode_request(strtolower($object . "." . $method), $params, array("encoding" => "UTF-8", "escaping" => "markup", "verbosity" => "no_white_space"));
        $header = array();
        $header[] = "Content-Type: text/xml";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "X-FORWARDED-FOR: " . @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->address);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookiefile);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_USERAGENT, "DomRobot/{$this->_ver} (PHP " . phpversion() . ")");
        $response = curl_exec($ch);
        curl_close($ch);
        if ($this->debug) {
            echo "Request:\n" . $request . "\n";
            echo "Response:\n" . $response . "\n";
        }
        return xmlrpc_decode($response, 'UTF-8');
    }

    /**
     * @param $secret
     * @return string
     */
    private function _getSecretCode($secret)
    {
        $_timeSlice = floor(time() / 30);
        $_codeLength = 6;

        $secretKey = $this->_base32Decode($secret);
        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $_timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretKey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashPart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashPart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $_codeLength);
        return str_pad($value % $modulo, $_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * @param $secret
     * @return string|false
     */
    private function _base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])
            ) {
                return false;
            }
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $secretCount = count($secret);
        $binaryString = "";
        for ($i = 0; $i < $secretCount; $i = $i + 8) {
            $x = "";
            if (!in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            $eightBitsCount = count($eightBits);
            for ($z = 0; $z < $eightBitsCount; $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
            }
        }
        return $binaryString;
    }

    /**
     * @return string[]
     */
    private function _getBase32LookupTable()
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', // 7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=' // padding char
        );
    }

    /**
     *  logout on destruct
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        $ret = $this->call('account', 'logout');
        if (file_exists($this->_cookiefile)) {
            unlink($this->_cookiefile);
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug = false)
    {
        $this->debug = (bool)$debug;
    }

    /**
     * @return string
     */
    public function getCookiefile()
    {
        return $this->_cookiefile;
    }

    /**
     * @param $file
     * @throws \Exception
     */
    public function setCookiefile($file)
    {
        if ((file_exists($file) && !is_writable($file)) || (!file_exists($file) && !is_writeable(dirname($file)))) {
            throw new \Exception("Cannot write cookiefile: '{$this->_cookiefile}'. Please check file/folder permissions.", 2400);
        }
        $this->_cookiefile = $file;
    }

    /**
     * @return string
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = (string)$customer;
    }

    /**
     * @return string
     */
    public function getClTrId()
    {
        return $this->clTRID;
    }

    /**
     * @param $clTrId
     */
    public function setClTrId($clTrId)
    {
        $this->clTRID = (string)$clTrId;
    }
}