<?php
class Daz_String {
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
     * Replace all occurrences of search tags in the search string with the
     * values from the replacement array.  We use a PHP 5.3 lambda function
     * which is called for each matched tag.
     */
    public static function merge($string, $replacements) {
        return preg_replace('/\[([^\]]+)\]/', function ($match) use ($replacements) {
            // tag being replaced
            $tag = $match[1];

            // matched tag is in our replacement array, use it
            if (isset ($replacements[$tag])) {
                return $replacements[$tag];
            }

            // no match in replacement array, leave tag as-is
            return '[' . $tag . ']';
        }, $string);
    }

    //----------------------------------------------------------------------
}