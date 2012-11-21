<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class String
{

    /**
     * Test whether a text string ends with a given string or not.
     * http://www.jonasjohn.de/snippets/php/ends-with.htm
     */
    public static function endsWith($haystack, $needle)
    {
        return strrpos($haystack, $needle) == strlen($haystack) - strlen($needle);
    }

    /**
     * Replace all occurrences of search tags in the search string with the
     * values from the replacement array.  We use a PHP 5.3 lambda function
     * which is called for each matched tag.
     */
    public static function merge($string, $replacements)
    {
        return preg_replace_callback(
            '/\[([^\]]+)\]/',
            function ($match) use ($replacements) {
                // tag being replaced
                $tag = $match[1];

                // matched tag is in our replacement array, use it
                if (isset ($replacements[$tag])) {
                    return $replacements[$tag];
                }

                // no match in replacement array, leave tag as-is
                return '[' . $tag . ']';
            },
            $string
        );
    }

    /**
     * Tests if a text string starts with a given string.
     * http://www.jonasjohn.de/snippets/php/starts-with.htm
     */
    public static function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
}