<?php
class Daz_Db_Connection_Example extends Daz_Db_Connection {
    protected static $CONN = null;

    //----------------------------------------------------------------------
    protected static function getAuth() {
        return array (
            'dsn' => Daz_Config :: get('database.site_dsn')
        );
    }

    //----------------------------------------------------------------------
}
