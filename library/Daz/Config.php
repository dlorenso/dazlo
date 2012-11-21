<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

use Daz\Debug;
use Daz\String;

class Config
{
    // config file flags
    private static $env = null;
    private static $custom = null;

    // config files will be loaded in the following order
    const CONFIG_FILE_SERVER = '[priv_dir]/config/server.ini';
    const CONFIG_FILE_ENV = '[priv_dir]/config/[env].ini';
    const CONFIG_FILE_CUSTOM = '[priv_dir]/config/[custom].ini';

    // config data storage
    private static $CONFIGS = array();

    /**
     * Having this function strays from our loose coupling policy, but we allow
     * it because this function should only be called when testing code and
     * it'll be commented out before going live, usually.
     */
    public static function dump()
    {
        Debug :: dump(print_r(self :: $CONFIGS, true));
    }

    public static function get($key, $default = '')
    {
        return isset (self :: $CONFIGS[$key]) ? self :: $CONFIGS[$key] : $default;
    }

    /**
     * Return the config value as a boolean.
     */
    public static function getBool($key, $default = false)
    {
        $v = strtolower(self :: get($key, $default));

        // we can only test scalar values
        if (!is_scalar($v)) {
            return (boolean) $v;
        }

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

    /**
     * Look up the config value then case it as an integer.  This is just
     * shorthand for handling integer-expectant config values.
     */
    public static function getInt($key, $default = '')
    {
        return intval(self :: get($key, $default));
    }

    /**
     * Extract all the keys from our configs which contains the same prefix.
     * This is an easy way to define an array of values under a
     * configuration section.  Optionally remove the prefix that was searched
     * for in cases where you just want to have the key/value subset.
     */
    public static function getPrefix($prefix, $strip_prefix = true)
    {
        // search all configs
        $data = array();
        foreach (self :: $CONFIGS as $key => $value) {
            // this is not our prefix, skip it
            if (strpos($key, $prefix) !== 0) {
                continue;
            }

            // strip off the search prefix from the key
            if ($strip_prefix) {
                $key = substr($key, strlen($prefix));
            }

            // we don't want to start or end with punctuation
            $key = trim($key, '._');

            // store the found key/value pair
            $data[$key] = $value;
        }

        // return matched values
        return $data;
    }

    /**
     * Read a single config file into our class config array.  This will parse
     * the INI file and flatten the keys as lowercase words separated by a
     * single underscore character.
     */
    private static function importConfig($ini_file)
    {
        // nothing to do if the file does not exist
        if (!file_exists($ini_file)) {
            return false;
        }

        // php loads INI files for us with it's built-in functions (use sections)
        $configs = (array) parse_ini_file($ini_file, true);

        // flatten nested arrays with lowercase and underscored naming convention
        $data = array();
        foreach ($configs as $section => $block) {
            // push flat keys into block format
            if (!is_array($block)) {
                $block = array(
                    $section => $block
                );
                $section = '';
            }

            // push all blocks into our config collection while flattening keys
            foreach ($block as $key => $value) {
                $key = $section ? ($section . '.' . $key) : $key;
                $key = strtolower(preg_replace('/[\s_]+/', '_', $key));
                $data[$key] = $value;
            }
        }

        // merge the imported config data into our class configs
        self :: $CONFIGS = array_merge(self :: $CONFIGS, $data);
        return true;
    }

    /**
     * Set the basic Dazlo constants as well as the "env" and "custom" flags
     * to identify which config files to load up. After defining these, go ahead
     * and load the configs since it's pretty certain we will be needing them.
     */
    public static function init($env, $custom)
    {
        // sanity check
        if (!$env || !$custom) {
            throw new Daz\Exception('Invalid config file initialization parameters!');
        }

        // init with hard constants
        $data = array(
            'env' => $env,
            'custom' => $custom,
            'docroot' => DOCROOT,
            'app_dir' => APP_DIR,
            'priv_dir' => PRIV_DIR,
            'root_dir' => ROOT_DIR
        );

        // start with the basics (might be used by macros)
        self :: $CONFIGS = $data;

        // import the SERVER config settings
        self :: importConfig(String :: merge(self :: CONFIG_FILE_SERVER, $data));

        // import the ENV config settings
        self :: importConfig(String :: merge(self :: CONFIG_FILE_ENV, $data));

        // import the CUSTOM config settings
        self :: importConfig(String :: merge(self :: CONFIG_FILE_CUSTOM, $data));

        // don't let our "core" values be overridden ... put them back in again
        self :: $CONFIGS = array_merge(self :: $CONFIGS, $data);

        // do macro replacements
        self :: replaceMacros();
    }

    /**
     * Checking to see if we are on the dev environment is so common, we can
     * create a shorthand for the lookup.  You must define the general.is_dev
     * value in your config file for this to work properly.
     */
    public static function isDev()
    {
        return self :: getBool('general.is_dev');
    }

    /**
     * Loop through all the config values and look for the pattern "{%...%}"
     * which indicates a macro replacement.  When found, replace with the looked
     * up value.  Continue doing replacements until no changes were made.
     */
    private static function replaceMacros($loop_count = 0)
    {
        // copy the class configs locally
        $configs = self :: $CONFIGS;
        $again = false;

        // loop through configs looking for macros, replace them if found
        foreach ($configs as $key => $value) {
            // find our macros and replace them if we have the values defined
            $match = array();
            $new_value = preg_replace_callback(
                '/{%(.*?)%}/',
                function ($match) use ($configs, $again) {
                    // found the replacement
                    if (isset ($configs[$match[1]])) {
                        $again = true;
                        return $configs[$match[1]];
                    }

                    // leave original tag as it was
                    return '{%' . $match[1] . '%}';
                },
                $value
            );

            // if the replaced string is not the same as the original, something changed and we need to run through the replacements all over again
            if ($value != $new_value) {
                $configs[$key] = $new_value;
                $again = true;
            }
        }

        // update class configs with processed values
        self :: $CONFIGS = $configs;

        // replace macros again (recursive macros)
        if ($again) {
            self :: replaceMacros($loop_count + 1);
        }
    }

}