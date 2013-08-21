<?php

/**
 * Lightweight db abstraction class
 */
class mydb {
    private $_conn;

    /**
     * DB connection factory method
     */
    public static function connect($dbtype, $dbhost, $dbuser, $dbpass, $dbname = '') {
        $classname = "mydb_{$dbtype}";
        if (!class_exists($classname)) {
            throw new Exception("Invalid dbtype '{$dbtype}'\n");
        }

        return new $classname($dbhost, $dbuser, $dbpass, $dbname);
    }
}

class mydb_pgsql extends mydb {
    /**
     * Create a postgres database connection
     *
     * @param string $dbhost Database host
     * @param string $dbuser Database user name
     * @param string $dbpass Database user password
     * @param string $dbname Database name (optional)
     */
    public function __construct($dbhost, $dbuser, $dbpass, $dbname='') {
        $dbnamestring = !empty($dbname) ? " dbname='{$dbname}'" : '';
        $this->_conn = pg_connect("host='{$dbhost}' user='{$dbuser}' password='{$dbpass}'{$dbnamestring}");

        // Check connection
        if (!$this->_conn) {
            throw new Exception("Failed to connect to PgSQL: " . pg_last_error($this->_conn));
        }
    }

    /**
     * Close the database connection
     */
    public function disconnect() {
        pg_close($this->_conn);
    }

    /**
     * Query the database, returning any results as an associative array
     *
     * @param string $sql SQL query to run
     * @return array Array of results or empty array if none
     */
    public function query($sql) {
        $result = pg_query($this->_conn, $sql);
        if (!$result) {
            throw new Exception("Error running query: " . pg_last_error($this->_conn));
        }
        $out = array();
        while ($row = pg_fetch_assoc($result)) {
            $out[] = $row;
        }
        pg_free_result($result);

        return $out;
    }

    /**
     * Drop a database. Will return false if the database doesn't exist
     *
     * @param string $dbname Name of the database to drop
     *
     */
    public function dropdb($dbname) {
        // make sure the database exists
        $sql = "SELECT * FROM pg_catalog.pg_database WHERE datname = '{$dbname}'";
        $result = $this->query($sql);
        if (empty($result)) {
            // database doesn't exist
            return false;
        }

        // kill any existing sessions
        $sql = "SELECT pg_terminate_backend(pg_stat_activity.procpid)
            FROM pg_stat_activity
            WHERE pg_stat_activity.datname = '{$dbname}'";
        $this->query($sql);

        // Now drop the database
        $sql = "DROP DATABASE \"{$dbname}\"";
        $this->query($sql);
    }

    /**
     * Create a database. Will fail with an exception if database already exists
     *
     * @param string $dbname Name for the database
     */
    public function createdb($dbname) {
        $sql = "CREATE DATABASE \"{$dbname}\" ENCODING = 'UTF8'";
        $this->query($sql);
    }
}

class mydb_mysql extends mydb {
    /**
     * Create a mysql database connection
     *
     * @param string $dbhost Database host
     * @param string $dbuser Database user name
     * @param string $dbpass Database user password
     * @param string $dbname Database name (optional)
     */
    public function __construct($dbhost, $dbuser, $dbpass, $dbname) {
        $this->_conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

        // Check connection
        if ($this->_conn->connect_error) {
            throw new Exception("Failed to connect to MySQL: " . $this->_conn->connect_error);
        }
    }

    /**
     * Close the database connection
     */
    public function disconnect() {
        $this->_conn->close();
    }

    /**
     * Query the database, returning any results as an associative array
     *
     * @param string $sql SQL query to run
     * @return array Array of results or empty array if none
     */
    public function query($sql) {
        $result = $this->_conn->query($sql);
        if (!$result) {
            throw new Exception("Error running query: " . $this->_conn->error);
        }

        $out = array();

        // not all queries return results
        if ($result === true) {
            return $out;
        }

        while ($row = $result->fetch_assoc()) {
            $out[] = $row;
        }
        $result->free();

        return $out;
    }

    /**
     * Drop a database. Will return false if the database doesn't exist
     *
     * @param string $dbname Name of the database to drop
     *
     */
    public function dropdb($dbname) {
        // check database exists
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}'";
        $result = $this->query($sql);
        if (empty($result)) {
            // database doesn't exist
            return false;
        }

        $sql = "DROP DATABASE `{$dbname}`";
        $this->query($sql);
    }

    /**
     * Create a database. Will fail with an exception if database already exists
     *
     * @param string $dbname Name for the database
     */
    public function createdb($dbname) {
        $sql = "CREATE DATABASE `{$dbname}` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        $this->query($sql);
    }
}

