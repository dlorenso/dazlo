<?php
/**
 * Dazlo Framework Copyright (c) 2011 D. Dante Lorenso.
 * All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz\Db;

use Daz\Db\Statement;

abstract class Connection
{
    /**
     * Start a new transaction using the underlying PDO object.
     */
    public static function beginTransaction()
    {
        // fetch PDO connection
        $pdo = self :: getConnection();

        try {
            // begin the transaction
            $pdo->beginTransaction();

            // success
            return true;
        } // catch error exception and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * Commit a transaction using the underlying PDO object.
     */
    public static function commit()
    {
        // get database connection
        $pdo = self :: getConnection();

        try {
            // commit transaction
            $pdo->commit();

            // success
            return true;
        } // catch error exception and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    protected static function connectionInit()
    {
        return static :: $CONN;
    }

    protected static function fail($error_message)
    {
        // write error message to error log
        error_log('DB ERROR:' . $error_message);

        // extract a backtrace and write it line-by-line to the error log
        $trace = debug_backtrace();
        foreach ($trace as $i => $bt) {
            if (is_null($bt)) {
                continue;
            }

            // defaults
            $bt['file'] = empty ($bt['file']) ? '<no file>' : $bt['file'];
            $bt['line'] = empty ($bt['line']) ? '<no line>' : $bt['line'];
            $bt['class'] = empty ($bt['class']) ? '' : $bt['class'];
            $bt['type'] = empty ($bt['type']) ? '' : $bt['type'];
            $bt['function'] = empty ($bt['function']) ? '<no function>' : $bt['function'];

            // text error
            $msg = sprintf(
                '[%d] %s:%d called %s%s%s',
                $i,
                $bt['file'],
                $bt['line'],
                $bt['class'],
                $bt['type'],
                $bt['function']
            );
            error_log($msg);
        }

        // if errors are not displayed to screen, stop here
        if (!ini_get('display_errors')) {
            return false;
        }

        // trigger a user-level notice
        trigger_error('DB ERROR: ' . $error_message . PHP_EOL, E_USER_NOTICE);
        return false;
    }

    /**
     * Get a new PDO connection by returning an existing connection or creating
     * a new one with the settings we like to use.
     */
    private static function getConnection()
    {
        // we already initialized our connection!
        if (static :: $CONN) {
            return static :: $CONN;
        }

        // fetch authentication information
        $auth = static :: getAuth();

        // create PDO connection object using username and password (like MYSQL)
        if (isset ($auth['dsn']) && isset ($auth['username']) && isset ($auth['password'])) {
            $pdo = new \PDO($auth['dsn'], $auth['username'], $auth['password']);
        }

        // create PDO connection object only using DSN to connect
        elseif (isset ($auth['dsn'])) {
            $pdo = new \PDO($auth['dsn']);
        }

        // unknown auth settings
        else {
            throw new \Daz\Exception('Invalid Database Authorization!');
        }

        // Dazlo Framework authors prefer lowercase and exception errors
        $pdo->setAttribute(\PDO :: ATTR_CASE, \PDO :: CASE_LOWER);
        $pdo->setAttribute(\PDO :: ATTR_ERRMODE, \PDO :: ERRMODE_EXCEPTION);

        // initialize and return connection
        static :: $CONN = $pdo;
        return static :: connectionInit();
    }

    /**
     * Generic method to perform common queries like UPDATE and DELETE when all
     * you need is the count of the number of affected rows from the statement.
     */
    private static function queryAffectedRows(Statement $stmt)
    {
        // get database connection
        $pdo = self :: getConnection();

        try {
            // we don't have a valid database connection
            if (!$pdo) {
                throw new \Daz\Exception('Invalid PDO Database Connection!');
            }

            // execute pdo statement
            $sth = $stmt->execute($pdo);

            // return a count of the number of rows affected by this query
            return $sth->rowCount();
        } // catch error exception and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * DELETE: Execute the given query and return the number of rows affected.
     */
    public static function queryDelete(Statement $stmt)
    {
        return self :: queryAffectedRows($stmt);
    }

    public static function queryInsert(Statement $stmt)
    {
        // get database connection
        $pdo = self :: getConnection();

        try {
            // execute pdo statement
            $sth = $stmt->execute($pdo);

            // no rows were inserted (insert failed!)
            if (!$sth->rowCount()) {
                return false;
            }

            // we don't use this method for PostgreSQL, so only MySQL and SQLite use this
            return $pdo->lastInsertId();
        } // catch error exception and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * UPDATE: Execute the given query and return the number of rows affected.
     */
    public static function queryUpdate(Statement $stmt)
    {
        return self :: queryAffectedRows($stmt);
    }

    /**
     * Rollback a transaction.
     */
    public static function rollback()
    {
        // get database connection
        $pdo = self :: getConnection();

        try {
            // roll back transaction
            $pdo->rollBack();

            // success
            return true;
        } // catch error exception and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * SELECT (type 1 of 3): Run the query and return an array of arrays.  The
     * 2nd argument uses the specified column as the key to the array, default
     * is enumerated array.  The 3rd argument uses the specified column as the
     * value to the array, default is the full row of data.
     */
    public static function selectMany(Statement $stmt, $index_column = '', $value_column = '')
    {
        // get database connection
        $pdo = self :: getConnection();

        // start with empty return data
        $data = array();

        try {
            // execute pdo statement
            $sth = $stmt->execute($pdo);

            // loop through all results as associative array
            while ($row = $sth->fetch(\PDO :: FETCH_ASSOC)) {
                // option 1: enumerated array for whole row
                if (!$index_column) {
                    $data[] = $row;
                }

                // option 2: indexed array for whole row
                elseif (!$value_column) {
                    $data[$row[$index_column]] = $row;
                }

                // option 3: indexed array for specific column
                else {
                    $data[$row[$index_column]] = $row[$value_column];
                }
            }

            // success!
            return $data;
        } // catch error execption and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * Select data as a multi-level nested array.  Parameters are the columns to
     * use as keys in the returned array.  Multiple keys accepted.
     */
    public static function selectNested(Statement $stmt)
    {
        // get database connection
        $pdo = self :: getConnection();

        // start with empty return data
        $data = array();

        // indexes
        $args = func_get_args();
        array_shift($args);

        try {
            // execute pdo statement
            $sth = $stmt->execute($pdo);

            // loop through all results as associative array
            while ($row = $sth->fetch(\PDO :: FETCH_ASSOC)) {
                $node = & $data;
                foreach ($args as $arg) {
                    $key = $row[$arg];
                    if (!isset ($node[$key])) {
                        $node[$key] = array();
                    }
                    // move to the new node
                    $node = & $node[$key];
                }

                // save the row
                $node = $row;
            }

            // success!
            return $data;
        } // catch error execption and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * SELECT (type 2 of 3): Run the query and return a single array of data
     * which is the first row of results returned by the query.
     */
    public static function selectRow(Statement $stmt)
    {
        // get database connection
        $pdo = self :: getConnection();

        try {
            // execute pdo statement
            $sth = $stmt->execute($pdo);

            // fetch the first row of data
            $data = $sth->fetch(\PDO :: FETCH_ASSOC);

            // success!
            return $data;
        } // catch error exception and fail
        catch (\Exception $ex) {
            return self :: fail($ex->getMessage());
        }
    }

    /**
     * SELECT (type 3 of 3): Run the query and return a single scalar value from
     * the first row and the specified column.
     */
    public static function selectValue(Statement $stmt, $column, $default = false)
    {
        // select the first row of data
        $row = self :: selectRow($stmt);

        // from the first row, read the value of the selected column, or use default
        return isset ($row[$column]) ? $row[$column] : $default;
    }

    /**
     * Shorthand for fetching a statement object without having to remember the
     * name of the statement class.
     */
    public static function statement()
    {
        return new Statement();
    }

}