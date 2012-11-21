<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class Loader
{
    public static function autoload($class_name)
    {
        /*
         * if we see a capital letter in the class name and later see an
         * underscore character, replace that underscore with a slash to
         * generate a file name.  This is common for Dazlo Framework as well as
         * Zend Framework and some others.
         */
        $file_name = preg_replace('/([A-Z][^_]*)_/', '\1/', $class_name) . '.php';

        // try including the file.  PHP will search the include path to find it.
        $result = include_once $file_name;
        return $result;

    }

    public static function register()
    {
        // get current include path as a PHP array
        $paths = preg_split('/' . PATH_SEPARATOR . '/', get_include_path(), 0, PREG_SPLIT_NO_EMPTY);

        /*
         * Array flatten input arguments and add all paths to our search path.
         * This allows us to use either scalar or array input to define search
         * paths.
         */
        $args = func_get_args();
        array_walk_recursive(
            $args,
            function ($value, $key) use (& $paths) {
                $paths[] = $value;
            }
        );

        // set our include path
        $search_path = join(PATH_SEPARATOR, array_unique($paths));
        set_include_path($search_path);

        // register our autoloader
        spl_autoload_register(
            array(
                __CLASS__,
                'autoload'
            ),
            false,
            false
        );
    }

}