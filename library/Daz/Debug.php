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

class Debug
{
    public static function dump($data)
    {
        // choose dump format (html or cli)
        $is_cli = defined('STDIN') && isset ($_SERVER['argc']);

        // HTML mode
        if (!$is_cli) {
            print '<pre>' . htmlspecialchars(print_r($data, true)) . '</pre>';
            return;
        }

        // CLI mode
        print_r($data);
        print "\n";
    }
}