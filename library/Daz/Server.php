<?php
/**
 * Dazlo Framework Copyright (c) 2011 D. Dante Lorenso.
 * All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class Server
{
    /**
     * Safely fetch a value from the PHP superglobal $_SERVER without throwing a
     * PHP warning about undefined keys.  If the key does not exist or is empty,
     * we will return a default value of our choosing or false otherwise.
     */
    public static function get($key, $default = null)
    {
        return empty ($_SERVER[$key]) ? $default : $_SERVER[$key];
    }

    /**
     * This should never be needed but was created to perform the opposite
     * function of a 'get'.  On some environments, it may be necessary to
     * override system defaults but setting $_SERVER values manually.
     */
    public static function set($key, $value)
    {
        $_SERVER[$key] = $value;
    }
}