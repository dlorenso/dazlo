<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class Format
{
    /**
     * Modified from http://kohanaframework.org/3.0/guide/api/Text
     */
    public static function bytes($bytes, $force_unit = null)
    {
        // eclipse wraps arrays and I don't want to have them wrap, so I'll push the units on
        $units = array();
        array_push($units, 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        // select proper units
        $power = ($bytes > 0) ? floor(log($bytes, 1000)) : 0;

        // format number and return
        return sprintf('%01.2f %s', $bytes / pow(1000, $power), $units[$power]);
    }
}