<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz;

class Tag extends \XMLWriter
{
    /**
     * Most of what this class is needing to do is already implemented in the
     * XMLWriter object which was added to PHP as of version 5.1.2. These
     * additional methods make it extremely easy to work with the class by
     * removing boiler-plate code and adding a lot of shorthand notation.
     */
    private $count_elements = 0;

    public function __construct()
    {
        // open the XMLWriter memory to begin collecting the XML output
        $this->openMemory();

        // turn on indentation for easy readability
        $this->setIndentString('  ');
        $this->setIndent(true);

        // the remainder of this constructor acts exactly like the  "push" method
        if (func_num_args() > 0) {
            call_user_func_array(
                array(
                    $this,
                    'push'
                ),
                func_get_args()
            );
        }
    }

    public function __toString()
    {
        // make sure all elements have been ended by processing remaining stack
        while ($this->count_elements > 0) {
            $this->pop();
        }

        // serialize the XML and return as a string
        return $this->outputMemory();
    }

    /**
     * Parameters are dynamic and represent an arbitrary number of key/value
     * pairs.  If an odd number of arguments are supplied, the last argument is
     * a boolean flag which determines if any of the arguments should be used.
     */
    public function attr()
    {
        // we can be called with dynamic number of arguments
        $args = func_get_args();

        // boolean flag is present!
        if (count($args) % 2) {
            // do nothing if the boolean fails
            if (!(boolean) array_pop($args)) {
                // enable chaining
                return $this;
            }
        }

        // add the attributes to our element
        for ($i = 0; $i < count($args); $i += 2) {
            $this->writeAttribute($args[$i], $args[$i + 1]);
        }

        // enable chaining
        return $this;
    }

    /**
     * Performs a 'push' and 'pop' operation in a single step for convenience.
     */
    public function append()
    {
        // push element
        call_user_func_array(
            array(
                $this,
                'push'
            ),
            func_get_args()
        );

        // pop element
        $this->pop();

        // enable chaining
        return $this;
    }

    /**
     * Ends the given number of "tag" nodes and reduces the stack counter as we
     * move our way back down the stack.
     */
    public function pop($count = 1)
    {
        for ($i = 0; $i < $count; $i++) {
            // end the "tag" element
            $this->endElement();
            $this->count_elements--; // decrement tag count
        }

        // enable chaining
        return $this;
    }

    /**
     * Create a new XML node with the given tag name.  If additional arguments
     * are supplied, the last argument is a text node.  Between the tag name and
     * text node, you may supply as many key/value pairs as desired to act as
     * attributes to be applied to the tag.
     */
    public function push($tagName)
    {
        // we can be called with dynamic number of arguments
        $args = func_get_args();

        // tagName is always required
        $tagName = array_shift($args);

        // text may exist if we have an even number of arguments
        $text = (count($args) % 2) ? array_pop($args) : null;

        // start a "tag" element
        $this->startElement($tagName);
        $this->count_elements++; // increment tag count

        // if there are arguments remaining, they are key/value attribute pairs
        for ($i = 0; $i < count($args); $i += 2) {
            $this->attr($args[$i], $args[$i + 1]);
        }

        // add the text node if it exists
        if (!is_null($text)) {
            $this->text($text);
        }

        // enable chaining
        return $this;
    }

    /**
     * Add already-generated XML data to our tag.  This avoids escaping the XML
     * characters and passes the text straight through as-is.
     */
    public function raw($rawxml)
    {
        // parent class implements this for us
        $this->writeRaw($rawxml);

        // enable chaining
        return $this;
    }

}