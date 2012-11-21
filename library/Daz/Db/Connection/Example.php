<?php
namespace Daz\Db\Connection;

use Daz\Db\Connection;
use Daz\Config;

class Example extends Connection
{
    protected static $CONN = null;

    protected static function getAuth()
    {
        return array(
            'dsn' => Config :: get('database.site_dsn')
        );
    }
}
