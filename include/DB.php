<?php
class DB_Factory
{
    private static $dbc = false;
    public static function getDatabase() : PDO
    {
        if( self::$dbc == false )
        {
            self::$dbc = new PDO("mysql:host=127.0.0.1;dbname=bbs", "bbs", "");
        }
        return self::$dbc;
    }
}
