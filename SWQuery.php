<?php
/**
 * SWQuery
 *
 * MIT License (MIT)
 *
 * Copyright (c) 2017, Satrio Wibowo
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @version     0.1.0-alpha
 * @author      Satrio Wibowo <satrio6166@gmail.com>
 * @copyright   Copyright (c) 2017, Satrio Wibowo
 * @license     http://opensource.org/licenses/MIT   MIT License
 */
class SWQuery {

    /**
     * Database configuration. contain MySQL user informations
     * such as username, password, etc.
     *
     * @var     array
     * @access  protected
     */
    protected $db_config = array();

    /**
     * MySQL Result object.
     *
     * @var     object
     * @access  protected
     */
    protected $mysqli_result = array();

    /**
     * Error messages.
     *
     * @var     string
     * @access  protected
     */
    protected $msg = array(
        'CONN_ERR' => array(
            'Connection Error',
            'Unable to connect to MySQL Server!. Check your username and password!'
        ),
        'DB_ERR'   => array(
            'Database Error',
            'Database "%dbnm" not found on this server. it has been droped or renamed!'
        ),
        'SQL_ERR'  => array(
            'SQL Error',
            '%error'
        )
    );

    /**
     * This property will contains many error messages
     * if you got many error in your sql command,
     * connection or database
     *
     * @var     array
     * @access  public
     */
    public $errors = array();

    /**
     * Constructor
     *
     * @return void
     * @access public
     */
    function __construct( $host, $user, $pass, $dbnm )
    {
        $this->db_config = array(
            'hostname' => $host,
            'username' => $user,
            'password' => $pass,
            'database' => $dbnm
        );
    }

    /**
     * Set errors
     *
     * @return void
     * @access public
     */
    public function error( $type, $args = null )
    {
        $title = $this->msg[$type][0];
        $desc = $this->msg[$type][1];
        $msg = "<b>$title :</b> $desc";

        if ( !empty($args) ) {
            $msg = '';
            foreach ( $args as $key => $value ) {
                $msg .= str_replace( "%$key", $value, $desc );
            }
            $msg = "<b>$title :</b> $desc";
        }

        $this->errors[] = $msg;
        return $this;
    }

    /**
     * Create and return MySQL Connection otherwise false
     *
     * @return resource|false
     * @access public
     */
    public function connect()
    {
        // Get required data
        $host = $this->db_config['hostname'];
        $user = $this->db_config['username'];
        $pass = $this->db_config['password'];
        $dbnm = $this->db_config['database'];

        // Making connection...
        $conn = mysqli_connect( $host, $user, $pass );
        // Checking connection status
        if ( $conn ) {
            // Use database
            $chdb = mysqli_select_db( $conn, $dbnm );
            // Check database status
            if ( !$chdb ) {
                // Set errors
                $this->error( 'DB_ERR', array('dbnm' => $dbnm) );
                // Database error, false
                return FALSE;
            }
        } else {
            // Set errors
            $this->error( 'CONN_ERR' );
            // Connection error, false
            return FALSE;
        }
        // Connected, true
        return $conn;
    }

    /**
     * Execute and return MySQL Result object otherwise false
     *
     * @param  string       $sql  SQL Command to execute
     * @param  string       $name Stack name
     * @return object|false
     * @access public
     */
    public function query( $name, $sql )
    {
        // Making connection first
        $conn = $this->connect();
        // Execute query
        $res = mysqli_query( $conn, $sql );
        // Checking executed query status
        if ( !$res ) {
            // Set errors
            $this->error( 'SQL_ERR', array('error' => mysqli_error($conn)) );
            // Close connection
            mysqli_close( $conn );
            // Query error, false
            return FALSE;
        }

        // Assign MySQLi Result object into mysqli_result property
        $this->mysqli_result[$name] = $res;
        // Close connection
        mysqli_close( $conn );
        // Query ok, this object
        return $this;
    }

    /**
     * if you has been execute query before, this method will
     * return MySQL Result object otherwise null
     *
     * @param  string       $name Stack name
     * @return object|null
     * @access public
     */
    public function result( $name )
    {
        $res = $this->mysqli_result[$name];
        // Unset selected mysqli result object from array stack
        unset( $this->mysqli_result[$name] );
        return $res;
    }

    /**
     * Fetch MySQL Result into associative array
     *
     * @param string $name Stack name
     * @return array
     * @access public
     */
    public function result_array( $name )
    {
        // Temporary
        $result = array();
        // Get MySQLi Result object
        $res = $this->mysqli_result[$name];
        // Checking, if MySQLi Result is not a object, false
        if ( !is_object($res) && !empty($res) ) return FALSE;
        // Fetching data...
        while ( $row = $res->fetch_assoc() ) {
            $result[] = $row;
        }
        // Unset selected mysqli result object from array stack
        unset( $this->mysqli_result[$name] );
        // Fetching complete, array
        return $result;
    }

    /**
     * Fetch MySQL Result into (one row) array
     *
     * @param string $name Stack name
     * @return array
     * @access public
     */
    public function result_row( $name )
    {
        // Get MySQLi Result object
        $res = $this->mysqli_result[$name];
        // Checking, if MySQLi Result is not a object, false
        if ( !is_object($res) && !empty($res) ) return FALSE;
        // Unset selected mysqli result object from array stack
        unset( $this->mysqli_result[$name] );
        // Fetch row, array
        return $res->fetch_row();
    }
}
// END OF CLASS
