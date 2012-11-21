<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class Session extends \Zend\Session\Container
{
    /**
     * Choose a namespace automatically if one is not already set.  The auto-
     * selected namespace will be related to the current page and if there is no
     * current page, we'll use 'DEFAULT'.
     */
    public function __construct($namespace = null)
    {
        // no namespace passed in, auto-select based on page or use default
        if (!$namespace) {
            $page = empty ($_SERVER['PHP_SELF']) ? 'DEFAULT' : $_SERVER['PHP_SELF'];
            $namespace = md5($page);
        }

        // call Zend session parent constructor
        parent :: __construct('N' . $namespace);
    }

    /**
     * Destroys the entire session regardless of the namespace.  This may be
     * useful for logging a user our entirely and destroying all session data.
     */
    public function destroy()
    {
        \Zend\Session :: destroy(false);

        // wish I didn't have to do this, session doesn't immediately go away, though
        $_SESSION = array();
    }

    /**
     * Normally you would just use the magic methods to set and get values, but
     * in some cases, you want to fetch a default value if the key you request
     * is not set.  Use 'get' to fetch with testing and defaults.
     */
    public function get($key, $default = null)
    {
        return empty ($this->$key) ? $default : $this->$key;
    }

    /**
     * Import values from an array into our namespace.
     */
    public function import($data)
    {
        $this->applySet('array_merge', $data);
    }

    /**
     * A common practice is to read a parameter from the $_REQUEST array and set
     * that value into the user's session.  This is handled seamlessly in one
     * call below.
     * If the request value exists, it replaces the session data. If the request
     * value does not exist, the session value is returned.
     */
    public function requestFilter($key, $default = false)
    {
        // fetch the value from our request data superglobal
        $value = isset ($_REQUEST[$key]) ? $_REQUEST[$key] : null;

        // superglobal holds no value
        if (is_null($value)) {
            // read value from session
            $value = $this->get($key, null);

            // session value is defined ... use it!
            if (null !== $value) {
                return $value;
            }

            // no session value exists either, use the default value
            $value = $default;
        }

        // save our value into the session and return it
        $this->$key = $value;
        return $value;
    }

}