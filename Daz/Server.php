<?php
class Daz_Server {
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
    public static function get($key, $default = null) {
        return empty ($_SERVER[$key]) ? $default : $_SERVER[$key];
    }

    //----------------------------------------------------------------------
    public static function set($key, $value) {
        $_SERVER[$key] = $value;
    }

    //----------------------------------------------------------------------
}