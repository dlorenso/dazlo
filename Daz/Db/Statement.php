<?php
class Daz_Db_Statement {
    /**
     * Dazlo Framework
     * Copyright (c) 2011 D. Dante Lorenso.  All Rights Reserved.
     *
     * This source file is subject to the new BSD license that is bundled
     * with this package in the file LICENSE.txt.  It is also available
     * through the world-wide web at this URL:
     * http://www.opensource.org/licenses/bsd-license.php
     */

    // internal storage for sql statement being generated
    private $_sql = array ();

    // we convert all placeholder values to named parameters to allow mixing types
    private $_params = array ();

    // count array positions for SQL code, bind positions (fill values), and search filters
    private $_sql_position = 0;
    private $_bind_position = 0;
    private $_filter_position = 0;

    //----------------------------------------------------------------------
    /**
     * Save the replacement data value along with the data type in our named
     * parameter array.
     */
    public function bind($name, $value, $type = PDO :: PARAM_STR) {
        // store the type and value in our name replacement array
        $this->_params[$name] = array (
            $type,
            $value
        );

        // enable chaining
        return $this;
    }

    //----------------------------------------------------------------------
    /**
     * I don't like coupling this method with the Daz_Debug object, but I'm
     * doing it anyhow because I'm lazy.  I'll get around to changing this some
     * other day.
     */
    public function dump() {
        Daz_Debug :: dump($this->getDebug());
        Daz_Debug :: dump($this->_sql);
        Daz_Debug :: dump($this->_params);
    }

    //----------------------------------------------------------------------
    /**
     * When queries are executed through this PDO layer, we send the prepared
     * SQL statement and bind values separately to the backend.  For debugging
     * purposes, we attempt to "fake" filling in the placeholder values where
     * they should go in the query in order to give developers a general idea
     * of what the query looks like that gets sent to the database.  THIS IS NOT
     * THE ACTUAL QUERY BEING SENT!
     */
    public function getDebug() {
        /*
         * for debugging purposes, we will try to "fake" the SQL query that gets
         * sent to the database backend.
         */
        $sql = join("\n", $this->_sql);

        // replace all of our named parameters with their actual values
        $params = $this->_params;
        return preg_replace_callback('/:[a-z0-9_]+/m', function ($matches) use ($params) {
            $key = $matches[0];

            // can't file this param
            if (!isset ($params[$key])) {
                return $key;
            }

            // here is the param
            list ($type, $value) = $params[$key];

            switch ($type) {
                case PDO :: PARAM_BOOL :
                    return $value ? 'TRUE' : 'FALSE';

                case PDO :: PARAM_NULL :
                    return 'NULL';

                case PDO :: PARAM_INT :
                    return (int) $value;

                case PDO :: PARAM_STR :
                default :
                    return "'" . mysql_escape_string($value) . "'";
            }
        }, $sql);
    }

    //----------------------------------------------------------------------
    public function execute(PDO $pdo) {
        // merge all lines of sql into a single statement and "prepare" with PDO object
        $stmt = $pdo->prepare(join(' ', $this->_sql));

        /*
         * Since all of our placeholder parameters have been converted to named
         * parameters, we loop through our named parameter array and bind all
         * the values to our PDO object ...
         */
        foreach ($this->_params as $name => $param) {
            list ($type, $value) = $param;

            switch ($type) {
                case PDO :: PARAM_BOOL :
                    $stmt->bindValue($name, (boolean) $value, $type);
                    break;

                case PDO :: PARAM_NULL :
                    $stmt->bindValue($name, null);
                    break;

                case PDO :: PARAM_INT :
                    $stmt->bindValue($name, $value, $type);
                    break;

                case PDO :: PARAM_STR :
                default :
                    $stmt->bindValue($name, (string) $value, $type);
                    break;
            }
        }

        // query has been prepared, and all parameters bound ... run the query
        $stmt->execute();

        // return PDO statement handle (to fetch results)
        return $stmt;
    }

    //----------------------------------------------------------------------
    public function generateName($type = false) {
        switch ($type) {
            case 'sql' :
                // automatic naming for positioned sql parameters
                return sprintf(':pos%d', $this->_sql_position++);

            case 'filter' :
                // auto naming for search filters
                return sprintf(':filter%d', $this->_filter_position++);

            case 'bind' :
            default :
                // auto naming for bind values
                return sprintf(':pos%d', $this->_bind_position++);
        }
    }

    //----------------------------------------------------------------------
    /**
     * Sets a "string" value for a positioned parameter.  All "set" functions
     * will go through the bind
     */
    public function set($value, $type = PDO :: PARAM_STR) {
        return $this->bind($this->generateName(), $value, $type);
    }

    //----------------------------------------------------------------------
    /**
     * Set a boolean value by testing the string value against known "true-ish"
     * values.  If the value looks true, use TRUE and if it looks false,
     * use FALSE.
     */
    public function setBoolean($value) {
        // we can only test scalar values
        if (is_scalar($value)) {
            $v = strtolower($value);

            // clearly it is TRUE
            if ($v == 'yes' || $v == 'true' || $v == 't' || $v == 'on' || $v === true) {
                $value = true;
            }

            // clearly it is FALSE
            elseif ($v == 'no' || $v == 'false' || $v == 'f' || $v == 'off' || $v === false) {
                $value = false;
            }
        }

        // its not clear, just cast it and be done with it
        return $this->set((boolean) $value, PDO :: PARAM_BOOL);
    }

    //----------------------------------------------------------------------
    /**
     * Cast the value as a float.  In PHP, this will ensure the value is a
     * number.  Otherwise, the result will be a "0".  Works with INT, TINYINT,
     * BIGINT, datatypes, etc.
     */
    public function setInt($value) {
        return $this->set((float) $value, PDO :: PARAM_INT);
    }

    //----------------------------------------------------------------------
    /**
     * Format the value as US currency.
     */
    public function setMoney($value) {
        return $this->set(sprintf('%0.2f', $value));
    }

    //----------------------------------------------------------------------
    public function setn($value) {
        // when the value is empty, send a NULL to the database
        if (!$value) {
            return $this->set(null, PDO :: PARAM_NULL);
        }

        // value is not empty, we can use its string value
        return $this->set($value, PDO :: PARAM_STR);
    }

    //----------------------------------------------------------------------
    public function setnInt($value) {
        // when the value is empty, send a NULL to the database
        if (!$value) {
            return $this->set(null, PDO :: PARAM_NULL);
        }

        // value is not empty, we can use its INT value
        return $this->setInt($value);
    }

    //----------------------------------------------------------------------
    /**
     * We loop through all the '?' placeholders and convert them to named
     * placeholders in order to allow us to mix named and positioned
     * placeholders in our queries.
     */
    public function sql($text) {
        // generate placeholder names for all of the question mark placeholders
        $myself = $this;
        $text = preg_replace_callback('/\?/m', function ($matches) use ($myself) {
            return $myself->generateName('sql');
        }, $text);

        // we might want to use the 'sprintf' format for filling in simple (safe) values
        if (func_num_args() > 1) {
            $text = call_user_func_array('sprintf', func_get_args());
        }

        // save the text as part of our SQL statement
        $this->_sql[] = $text;

        // enable chaining
        return $this;
    }

    //----------------------------------------------------------------------
}