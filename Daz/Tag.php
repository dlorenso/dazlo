<?php
class Daz_Tag extends XMLWriter {
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
     * Most of what this class is needing to do is already implemented in the
     * XMLWriter object which was added to PHP as of version 5.1.2. These
     * additional methods make it extremely easy to work with the class by
     * removing boiler-plate code and adding a lot of shorthand notation.
     */
    private $count_elements = 0;

    //----------------------------------------------------------------------
    public function __construct() {
    }

    //----------------------------------------------------------------------
    public function __toString() {
    }

    //----------------------------------------------------------------------
    public function attr() {
    }

    //----------------------------------------------------------------------
    public function append() {
    }

    //----------------------------------------------------------------------
    public function pop() {
    }

    //----------------------------------------------------------------------
    /**
     * Create a new XML node with the given tag name.  If additional arguments
     * are supplied, the last argument is a text node.  Between the tag name and
     * text node, you may supply as many key/value pairs as desired to act as
     * attributes to be applied to the tag.
     */
    public function push($tagName) {
        // we can be called with dynamic number of arguments
        $args = func_get_args();

        // tagName is always required
        $tagName = array_shift($args);

        // text may exist if we have an even number of arguments
        $text = (count($args) % 2) ? array_pop($args) : null;

        // start a new "tag" element
        $this->startElement($tagName);
        $this->count_elements++;

        // if there are arguments remaining, they are key/value attribute pairs
        for ($i = 0; $i < count($args); $i += 2) {
            $this->attr($args[$i], $args[$i +1]);
        }

        // add the text node if it exists
        if (!is_null($text)) {
            $this->text($text);
        }

        // enable chaining
        return $this;
    }

    //----------------------------------------------------------------------
    /**
     * Add already-generated XML data to our tag.  This avoids escaping the XML
     * characters and passes the text straight through as-is.
     */
    public function raw($rawxml) {
        // parent class implements this for us
        $this->writeRaw($rawxml);

        // enable chaining
        return $this;
    }

    //----------------------------------------------------------------------
}