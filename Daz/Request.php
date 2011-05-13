<?php
class Daz_Request {
    /**
     * Dazlo Framework
     * Copyright (c) 2011 D. Dante Lorenso.  All Rights Reserved.
     *
     * This source file is subject to the new BSD license that is bundled
     * with this package in the file LICENSE.txt.  It is also available
     * through the world-wide web at this URL:
     * http://www.opensource.org/licenses/bsd-license.php
     */

    //----------------------------------------------------------------------
    /**
     * Fetch and return an element from the Request array and return it.  If it
     * is not set, use the default value.
     */
    public static function get($key, $default = false) {
        return isset ($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    //----------------------------------------------------------------------
    /**
     * Fetch an element from the Request array and return it as a boolean value.
     */
    public static function getbool($key, $default = false) {
        $v = self :: get($key, $default);

        // we can only test scalar values
        if (!is_scalar($v)) {
            return (boolean) $v;
        }

        // case-insensitive checking
        $v = strtolower($v);

        // clearly it is TRUE
        if ($v == 'yes' || $v == 'true' || $v == 't' || $v == 'on' || $v === true) {
            return true;
        }

        // clearly it is FALSE
        if ($v == 'no' || $v == 'false' || $v == 'f' || $v == 'off' || $v === false) {
            return false;
        }

        // its not clear, just cast it and be done with it
        return (boolean) $v;
    }

    //----------------------------------------------------------------------
}