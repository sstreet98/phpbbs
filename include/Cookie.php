<?php

class AuthException extends Exception
{
}

class Cookie
{
    private $created;
    private $userid;
    private $version;

    static private $cypher_algo =   'blowfish';
    static private $passphrase    = 'N@styK3y';

    static $COOKIENAME = 'AUTHCOOKIE';
    static $COOKIEVERSION = 1;
    static $EXPIRATION = 1200;
    static $WARNING = 600;
    static $GLUE = "|";

    public function __construct($userid = false)
    {
        if ($userid != false) {
            $this->userid = $userid;
            return;
        } else {
            if (array_key_exists(self::$COOKIENAME, $_COOKIE)) {
                $this->_unpackage($_COOKIE[self::$COOKIENAME]);
            } else {
                throw new AuthException("No Cookie ");
            }
        }
    }

    public function set(): void
    {
        setcookie(self::$COOKIENAME, $this->_package());
    }

    public function validate(): void
    {
        if (!$this->version || !$this->created || !$this->userid) {
            throw new AuthException("Malformed Cookie");
        }

        if ($this->version != self::$COOKIEVERSION) {
            $this->logout();
            throw new AuthException("Cookie version mismatch");
        }

        if (time() - $this->created  > self::$EXPIRATION) {
            $this->logout();
            throw new AuthException("Cookie expired");
        }

        if (time() - $this->created > self::$WARNING) {
            // renew cookie
            $this->set();
        }
    }

    public function logout(): void
    {
        setcookie(self::$COOKIENAME, "", 0);
    }

    public function id(): string
    {
        return $this->userid;
    }

    private function _package(): string
    {
        $this->version = self::$COOKIEVERSION;
        $this->created = time();
        $rawdata = array($this->version, $this->created, $this->userid);
        $cookiedata = implode(self::$GLUE, $rawdata);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cypher_algo));
        $crypt = openssl_encrypt($cookiedata, self::$cypher_algo, self::$passphrase, 0, $iv);
        return implode(self::$GLUE, array($iv, $crypt));
    }

    private function _unpackage($COOKIE): void
    {
        list($iv, $crypt) = explode(self::$GLUE, $COOKIE);
        $cookiedata = openssl_decrypt($crypt, self::$cypher_algo, self::$passphrase, 0, $iv);
        $data =  explode(self::$GLUE, $cookiedata);
        list($this->version, $this->created, $this->userid) = $data;

        if (
            $this->version != self::$COOKIEVERSION ||
            !$this->created ||
            !$this->userid
        ) {
            throw new AuthException("Could not decypher cookie data");
        }
    }
}
