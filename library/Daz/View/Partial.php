<?php
class Daz_View_Partial {
    /**
     * Dazlo Framework
     * Copyright (c) 2011 D. Dante Lorenso.  All Rights Reserved.
     *
     * This source file is subject to the new BSD license that is bundled
     * with this package in the file LICENSE.txt.  It is also available
     * through the world-wide web at this URL:
     * http://www.opensource.org/licenses/bsd-license.php
     */

    /**
     * This class is a simple glue layer between old "views" and the Zend
     * Framework style of rendering view partials. Using the "render" function,
     * you can render a Zend View partial in one line of code and not worry
     * about all the setup and instantiation of the object.
     */

    //----------------------------------------------------------------------
    private static function view($search_path = null) {
        static $cache_view = null;
        static $cache_search_path = null;

        // new view object
        if (!$cache_view) {
            $cache_view = new Zend_View();
        }

        // change search path
        if ($search_path && $cache_search_path != $search_path) {
            $cache_view->setScriptPath($search_path);
        }

        // return view
        return $cache_view;
    }

    //----------------------------------------------------------------------
    /**
     * Leverage a Zend_View object to render the given php view as a partial
     * with the provided options.  This code wraps the Zend_View object
     * minutia so we can focus on just the easy stuff.
     */
    public static function render($path, $args) {
        // search for views in this directory
        $search_path = dirname($path);

        // render this view
        $view_script = basename($path);

        // render the view
        $view = self :: view($search_path);
        return $view->partial($view_script, $args);
    }

    //----------------------------------------------------------------------
}