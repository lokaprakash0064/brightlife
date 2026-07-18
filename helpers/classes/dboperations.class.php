<?php

/**
 * Class file to handle database CRUD operations
 * like insert, update, delete, drop etc.
 * to be used in every database actions
 * to connect to database server and commonly used variables and constants
 *
 * Used PDO class to automate and faster access.
 * 
 * PHP version 5.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   DbOperations
 * @package    TBF
 * @author     Kirti Kumar Nayak <admin@thebestfreelancer.in>
 * @copyright  2015 The Best Freelancer
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    GIT: $Id$
 * @link       http://repo.thebestfreelancer.in
 * @since      File available since Release 1.0
 * @deprecated File deprecated in Release 1.0
 */

// Check if DbOperations class exists or not and define if not
if (!class_exists('DbOperations')) {
    
    /**
     * Class to handle database CRUD operations
     * like insert, update, delete, drop etc.
     * to be used in every database actions
     * to connect to database server and commonly used variables and constants
     *
     * Used PDO class to automate and faster access.
     * 
     * PHP version 5.5
     *
     * @category   DbOperations
     * @package    TBF
     * @author     Kirti Kumar Nayak <admin@thebestfreelancer.in>
     * @copyright  2015 The Best Freelancer
     * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
     * @version    Release: @package_version@
     * @link       http://repo.thebestfreelancer.in
     * @since      Class available since Release 1.0
     * @deprecated Class deprecated in Release 1.0
     */

    class DbOperations
    {
        // {{{ properties
        
        /**
         * To store public database connection object
         * 
         * @access public
         * @var    mixed The database connection object
         * @static
         */
        public static $conn;

        /**
         * To store a MySQL result
         * 
         * @access public
         * @var    mixed The result object from a prepared query
         */
        public $res;

        /**
         * To store affected rows by a query
         * 
         * @access private
         * @var    int    The number of affected rows by a query
         */
        public $aff;

        /**
         * To store queries
         * 
         * @access public
         * @var    string The sql string to be prepared and executed
         */
        public $sql;

        /**
         * To store the fetched rows from a database
         * 
         * @access public
         * @var    array  The rows fetched by an SQL
         */
        public $rows;

        /**
         * To store database fields fetched or to be inserted into a table
         * 
         * @access public
         * @var    mixed  The database table column names
         */
        public $fields;
        
        /**
         * To store database fields to be used in where clause
         * 
         * @access public
         * @var    mixed  The database table column names
         */
        public $conditionalFields;

        /**
         * To store database fields' values fetched or to be inserted into a table
         * 
         * @access public
         * @var    mixed  The values to be stored or fetched from a query
         */
        public $values;

        /**
         * To store class instance object
         * 
         * @access private
         * @var    object  The current class singleton object
         * @static
         */
        private static $_classObject;

        // }}}
        // {{{ __construct()
        
        /**
         * Default constructor class to initialize variables and page data.
         * Accoring to singleton class costructor must be private
         * 
         * @category Constructor
         * @return   void
         * @access   private
         */
        private function __construct()
        {
            // initialize a null connection
            self::$conn            = null;
            // automatically called when object of this class created
            // initialize sql stastement as null
            $this->sql             = null;
            // initialize resultset to null
            $this->res             = null;
            // initialize affected rows as 0
            $this->aff             = 0;
            // initialize fetched rows as a blank array
            $this->rows            = array();
            // initialize the user given fields and data as blank arrays
            $this->fields          = array();
            $this->values          = array();
            /*
             * Connect to mysql through predefined constants using PDO class
             * with autocommit off and use buffered query options set true
             * and attributes set to catch errors and exceptions
             */
            try{
                if (!is_resource(self::$conn)){
                    self::$conn         = new PDO(
                        DRIVER .
                        ':host='.DBHOST.
                        ';dbname='.DBNAME,
                        DBUSER,
                        DBPASS,
                        array(
                            PDO::ATTR_AUTOCOMMIT => false,
                            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                            PDO::ATTR_PERSISTENT => false
                        )
                    );
                    self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                // if database connection was successful,
                // return the connection variables to the calee
                return self::$conn;
            }
            // else catch any exceptions and set and error message
            catch(PDOException $ex) {
                application_error($ex, 'DB connection failed');
            }
        }

        // }}}
        // {{{ transaction()
        
        /**
         * When Database is Connected successfully,
         * Public function for General Database transaction Operations
         * to commit rollback etc
         * Caution : transaction can not be handled
         * if the specified table does not InnoDB storage engine
         * 
         * @param string $opt The option to commit or rollback the transaction
         * 
         * @return   void  It should not return any value as it is operational only
         * @category DAO
         */
        public function transaction($opt = '')
        {
            try {
                // update opt var to lowercase
                $opt = (($opt === '')? '' : strtolower($opt));
                // check contents of $opt
                switch ($opt) {
                // if it is off or OFF then set the autocommit mode of the MySQL Off
                case('start'):
                    // check if inside any transaction or not
                    if (self::$conn->inTransaction() === false) {
                        // start a transaction
                        self::$conn->beginTransaction();
                    }
                    break;
                // if it is on or ON then commit the transaction and database table is affected
                case('on'):
                    self::$conn->commit();
                    break;

                // by default rollback the action
                default:
                    self::$conn->rollBack();
                    break;
                }
            }
            catch(Exception $ex) {
                application_error($ex, 'DB transaction failed');
            }
        }

        // }}}
        // {{{ buildInsertQuery()
        
        /**
         * Method for insert query builder for database
         * using PDO class and prepare statements for faster operations
         * $tablename contains name of the table in which data to be inserted
         * $fields contains name of the fields of the table which are to be inserted
         * 
         * @param string $tablename for the table in which data to be inserted
         * @param array  $fields    for the specified fields if desired
         * 
         * @access   public
         * @return   mixed resource of prepared query
         * @category model
         */
        public function buildInsertQuery($tablename = '', $fields = array())
        {
            // if table name empty or name given is an array
            if (empty($tablename) or is_array($tablename)) {
                // write the error to log
                die('Oops.. you forgot to write the table name to insert data. At line: '. __LINE__ .' in file: '. __FILE__);
            }
            
            // get the names of the fields of the table
            $columns                = $this->fetchData('SHOW COLUMNS FROM '.$tablename);
            // initialize an array variable to keep the field names
            $columnNames            = array();
            // extract the field names
            foreach ($columns as $key => $value) {
                // as the names of the fields are fetched as "Field", we can easily  get them
                $columnNames[$key]  = $value['Field'];
            }
            // initialize a PDO sql query for prepare statement
            $this->sql             = 'INSERT INTO ' . $tablename;
            $this->fields =array();
            // extract the field names and their respective value given by the user
            foreach ($fields as $key => $value) {
                // check if the value supplied is an array
                if (is_array($value)) {
                    // skip the field to be inserted
                    continue;
                } else {
                    // check if user has defined the fields according to the fields present in
                    // the table otherwise do not keep the user defined fields
                    in_array($value, $columnNames) ? array_push($this->fields, $value) : false;
                }
            }
            /*
             * check if the user given table field names array
             * is of same legth as vales given else discard the user defined fields 
             * & assume that user wants to insert into all fields of the table
             */
            if (count($this->fields) > 0) {
                /*
                 * if user has given equal number of fields and respective values, then build an insert query with fields
                 * this may help user to insert values into desired columns or less number of fields
                 */
                $this->sql .= ' (' . implode(', ', $this->fields) . ') VALUES (';
                // now run a loop to place ? marks for each of the values as used for prepare statement
                for ($i = 0; $i < count($this->fields); ++$i) {
                    // add ? mark and comma for separation
                    $this->sql     .= '?, ';
                }
            } else {
                // otherwise build an insert query to insert into all columns of the table
                $this->sql         .= ' VALUES (';
                // now run a loop to place ? marks for each of the values as used for prepare statement
                for ($i = 0; $i < count($columnNames); ++$i) {
                    // add ? mark and comma for separation
                    $this->sql     .= '?, ';
                }
            }
            // remove extra last comma which is unusable and close the common bracket started for values
            $this->sql             = substr($this->sql, 0, (strlen($this->sql) - 2)) . ')';
            // finally prepare the query and return
            return $this->prepareQuery();
        }
        
        // }}}
        // {{{ buildUpdateQuery()
        
        /**
         * Method for update query builder for database
         * using PDO class and prepare statements for faster operations
         * $tablename contains name of the table in which data to be inserted
         * $fields contains name of the fields of the table to be updated
         * $conditionalFields contains name of the fields of the table to be 
         * checked in where clause else the whole table gets updated
         * 
         * @param string $tablename         for the table in which data to be inserted
         * @param mixed  $fields            for the specified fields if desired
         * @param mixed  $conditionalFields for the specified fields in  where clause condition
         *                                                              where clause condition
         * 
         * @access   public
         * @return   mixed resource of prepared query
         * @category model
         */
        public function buildUpdateQuery($tablename = '', $fields = array(), $conditionalFields = array())
        {
            // if table name empty or name given is an array
            if (empty($tablename) or is_array($tablename)) {
                // write the error to log
                die('Oops.. you forgot to supply the table name to update data. At line: '. __LINE__ .' in file: '. __FILE__);
            }
            if (empty($fields) or !is_array($fields) or (count($fields) < 1)) {
                // write the error to log
                die('Oops.. you forgot to supply the table column names to update data. At line: '. __LINE__ .' in file: '. __FILE__);
            }
            if (empty($conditionalFields) or !is_array($conditionalFields) or (count($conditionalFields) < 1)) {
                // write the error to log
                die('Oops.. you forgot to supply the table column names for where clause to update data. At line: '. __LINE__ .' in file: '. __FILE__);
            }
            
            // get the names of the fields of the table
            $columns                = $this->fetchData('SHOW COLUMNS FROM '.$tablename);
            // initialize an array variable to keep the field names
            $columnNames            = array();
            // extract the field names
            foreach ($columns as $key => $value) {
                // as the names of the fields are fetched as "Field", we can easily  get them
                $columnNames[$key]  = $value['Field'];
            }
            // initialize a PDO sql query for prepare statement
            $this->sql             = 'UPDATE ' . $tablename . ' SET ';
            $this->fields =array();
            // extract the field names given by the user
            foreach ($fields as $key => $value) {
                // check if the value supplied is an array
                if (is_array($value)) {
                    // skip the field to be inserted
                    continue;
                } else {
                    // check if user has defined the fields according to the fields present in
                    // the table otherwise do not keep the user defined fields
                    (in_array($value, $columnNames) !== false) ? array_push($this->fields, $value) : false;
                    //var_dump(array_search($key, $columnNames));
                }
            }//var_dump($this->fields);exit;
            $this->conditionalFields =array();
            // extract the field names to be checked in where clause
            foreach ($conditionalFields as $key => $value) {
                // check if the value supplied is an array
                if (is_array($value)) {
                    // skip the field to be inserted
                    continue;
                } else {
                    // check if user has defined the fields according to the fields present in
                    // the table otherwise do not keep the user defined fields
                    in_array($value, $columnNames) ? array_push($this->conditionalFields, $value) : false;
                }
            }
            
            /*
             * check if the user given table field names array
             * is of same legth as vales given else discard the user defined fields 
             * & assume that user wants to insert into all fields of the table
             */
            if ((count($this->fields) > 0) and (count($this->conditionalFields) > 0)) {
                // append the set and where clause in the query
                $this->sql .= implode(' = ?, ', $this->fields) . ' = ? WHERE ';
                $this->sql .= implode(' = ? and ', $this->conditionalFields) . ' = ?';
            } else {
                // append the set and where clause in the query
                $this->sql .= implode(' = ?, ', $this->fields) . ' = ?';
            }
            // finally prepare the query and return
            return $this->prepareQuery();
        }
        // }}}
        // {{{ buildDeleteQuery()
        
        /**
         * Method for delete query builder for database
         * using PDO class and prepare statements for faster operations
         * $tablename contains name of the table in which data to be inserted
         * $condFields contains name of the fields of the table 
         * to be checked for conditions
         * 
         * @param string $tablename  for the table in which data to be inserted
         * @param array  $condFields for the specified fields if desired
         * 
         * @access   public
         * @return   mixed resource of prepared query
         * @category model
         */
        public function buildDeleteQuery($tablename = '', $condFields = array())
        {
            // if table name empty or name given is an array
            if (empty($tablename) or is_array($tablename)) {
                // write the error to log
                die('Oops.. you forgot to write the table name to insert data. At line: '. __LINE__ .' in file: '. __FILE__);
            }
            if (empty($condFields) or !is_array($condFields) or (count($condFields) < 1)) {
                // write the error to log
                die('Oops.. you forgot to supply the table column names for where clause to delete data. At line: '. __LINE__ .' in file: '. __FILE__);
            }
            
            // get the names of the fields of the table
            $columns                = $this->fetchData('SHOW COLUMNS FROM '.$tablename);
            // initialize an array variable to keep the field names
            $columnNames            = array();
            // extract the field names
            foreach ($columns as $key => $value) {
                // as the names of the fields are fetched as "Field", we can easily  get them
                $columnNames[$key]  = $value['Field'];
            }
            // initialize a PDO sql query for prepare statement
            $this->sql             = 'delete from ' . $tablename . ' where ';
            $this->fields =array();
            // extract the field names and their respective value given by the user
            foreach ($condFields as $key => $value) {
                // check if the value supplied is an array
                if (is_array($value)) {
                    // skip the field to be inserted
                    continue;
                } else {
                    // check if user has defined the fields according to the fields present in
                    // the table otherwise do not keep the user defined fields
                    in_array($value, $columnNames) ? array_push($this->fields, $value) : false;
                }
            }
            /*
             * check if the user given table field names array
             * is of same legth as vales given else discard the user defined fields 
             * & assume that user wants to insert into all fields of the table
             */
            if (count($this->fields) > 0) {
                // add where clause
                $this->sql .= implode(' = ? and ', $this->fields) . ' = ?';
            } else {
                // otherwise exit with error message
                die('Please specify some fields to be checked for deletion');
            }
            // finally prepare the query and return
            return $this->prepareQuery();
        }
        
        // }}}
        // {{{ prepareQuery()
        
        /**
         * Method to prepare a query
         * 
         * @param string $sql which is to be prepared
         * 
         * @access   public
         * @return   mixed resource of prepared query
         * @category model
         */
        public function prepareQuery($sql = '')
        {
            // check if the sql is not blank, take supplied sql
            if ($sql !== '') {
                $this->sql          = $sql;
            }
            // try to execute the SQL
            try {
                // begin transaction prepare SQL statement
                $this->res          = self::$conn->prepare($this->sql);
                return $this->res;
            }
            /*
             * otherwise catch exception and rollback the insert action
             */
            catch(PDOException $ex) {
                application_error($ex, 'DB query preparation failed - SQL: ' . $this->sql);
            }
        }


        // }}}
        // {{{ getObject()

        /**
         * Here we are trying to get a singleton instance of this class
         * it's very much useful for enhanced speed and low overhead
         * on database connections and variable memory allocations
         * $classObject is a private variable to be used in getObject()
         * to be returned on call as one and only instance of the class
         * getInstance uses this to get a pre initialized instance
         * or new instance if not initializes of the class
         * and through that we can get all other methods called
         * easily and friendly for usage and no need to remember
         * 
         * @access   public
         * @static
         * @category instance
         * @return   object   The current class object / instance
         */
        public static function getObject()
        {
            // check if class not instantiated
            if (self::$_classObject === null) {
                // then create a new instance
                self::$_classObject = new self();
            }
            // return the class object to be used
            return self::$_classObject;
        }

        // }}}
        // {{{ runQuery()
        
        /**
         * Publicly defined method to run the queries like:
         * update, delete, drop, etc.
         * just pass the query and leave ? (question mark) at values places
         * then pass the respective values in an array
         * Here I've tried to use prepare and execute instead of exec
         * as prepared statements are fast enough to run and they are also buffered
         * hence giving more speed
         *
         * This function returns the number of rows affected by the SQL else false and error
         * 
         * @param mixed $values The values to be replaced/bound when the query is run
         * @param mixed $preparedQueryObj The Prepared query object to be run
         * 
         * @access   public
         * @category model
         * @return   boolean if query execution success then true else false
         */
        public function runQuery($values = array(), $preparedQueryObj = null)
        {
            // check if values array is of 0 length and set a message
            if (count($values) < 1) {
                die('Oops... You forgot to set values: '. __LINE__ .' in file: '. __FILE__);
            }
            if (!is_array($values)) {
                die('Oops... I only accept data array for prepared query: '. __LINE__ .' in file: '. __FILE__);
            }
            if (($this->res === null)  and ($preparedQueryObj === null)) {
                die('Please prepare the query first to run');
            }
            if ($preparedQueryObj !== null) {
                $this->res = $preparedQueryObj;
            }
            foreach ($values as $key => $value) {
                if (is_array($value) and (count($value) > 0)) {
                    $this->runQuery($value);
                } else {
                    continue;
                }
            }
            // prepare and run the SQL
            try {
                //var_dump($values);
                // execute with the values to be inserted
                if ($this->res->execute($values)) {
                    return self::$conn->lastInsertId();
                } else {
                    $this->transaction('off');
                    return false;
                }
            }
            // else catch the error and return false
            catch(Exception $ex) {
                // roll back the transaction
                $this->transaction('off');
                application_error($ex, 'DB query execution failed - SQL: ' . $this->sql);
            }
        }

        // }}}
        // {{{ fetchData()

        /**
         * This method meant to fetch data from database table
         * just pass the SQL statement thru the function
         * it returns an array to the calle if successfully fetched the data
         * else it dies with an error message and returns false
         *
         * Used fetching type both array and associative with auto scroll cursor to next record
         * to be faster and scrollable data object
         * 
         * @param string $sqlString Which to be prepared
         * @param mixed  $values    Which to be executed with the prepared query
         * 
         * @access   public
         * @category model
         * @return   mixed Array of data if query run successfully else false
         */
        public function fetchData($sqlString = '', $values = array(), $prepare = true, $preparedQueryObj = null)
        {
            // start a try catch block to catch all errors and exceptions
            try {
                // assign the sql string to the class variable
                if ($sqlString !== '') {
                    $this->sql      = $sqlString;
                }
                // check if already prepared query available
                if ($prepare) {
                    // prepare and execute the sql statement
                    $this->res          = $this->prepareQuery($this->sql);
                } else {
                    if ($preparedQueryObj === null) {
                        throw new Exception('Please pass a prepared query');
                    } else {
                        $this->res      = $preparedQueryObj;
                    }
                }
                // check if data passed for prepare query
                if (is_array($values) and count($values) > 0) {
                    // prepare and execute the sql statement
                    $this->aff      = $this->res->execute($values);
                } else {
                    $this->aff      = $this->res->execute();
                }
                if ($this->aff) {
                    // initialize the rows as an empty array
                    $this->rows    = array();
                    // start data fetching with loop and set fetching type both
                    // array and associative with auto scroll cursor to next record
                    $this->rows     = $this->res->fetchAll();
                    return $this->rows;
                } else {
                    return false;
                }
            }

            catch(Exception $ex) {
                application_error($ex, 'DB data fetch failed - SQL: ' . $this->sql);
            }
        }

         // }}}
        // {{{ countRows()
         
        /**
         * Public method to count the rows to be fetched by any query
         * Takes SQL string as argument and uses PDO class functions to evaluate
         * if no SQL passed through arguments, it dies with error
         *
         * Returns number of rows to be fetched by a SQL statement if all goes correct
         * Else returns false
         * 
         * @param string $sqlString the SQL string to be executed
         * 
         * @category model
         * @access   public
         * @return   int the number of rows to be fetched by the query
         */
        public function countRows($sqlString) 
        {
            return count($this->fetchData($sqlString));
        }

        // }}}
        // {{{ optimizeDatabase()
        
        /**
         * Public function to optimize database tables
         * should be run once a day manually through admin panel
         * otherwise you can also set a cron job for this purpose
         * just call the function and it does everything to optimize a database and the tables inside
         *
         * WARNING: DO THIS IN OFF HOURS WHEN LESS OR NO USERS ARE THERE, 
         * IT LOCKS THE TABLES TO OPTIMIZE THEM ###########
         *
         * Usage:
         * DbOperations::getObject()->optimizeDatabase();
         *
         * @return   integer The no of tables optimized
         * @category model
         * @access   public
         */
        public function optimizeDatabase()
        {
            // search for tables with more than 10% overhead and more than 100k of free space
            $this->sql              = 'SHOW TABLE STATUS WHERE Data_free / Data_length > 0.1 AND Data_free > 102400';
            // fetch the data
            $this->rows             = $this->fetchData($this->sql);
            // set a variable to count the number of tables optimized
            $count                  = 0;
            // run the query with transaction
            $this->transaction('off');
            // loop through the tables and optimize them
            foreach ($this->rows as $rows) {
                // write query to optimize the table
                $this->sql         = 'OPTIMIZE TABLE '.$rows[0]['Name'];
                $this->res         = self::$conn->query($this->sql);
                if ($this->res) {
                    ++$count;
                }
            }
            $this->transaction('on');
            return $count;
        }

        // }}}
        // {{{ __destruct()
        
        /**
         * The default destructor of the class,
         * to disconnect database and objects created
         * 
         * @access public
         * @return void Only destroys variables, nothing returned
         */
        public function __destruct() 
        {
            // check if database connection is present and make it null
            if (is_resource(self::$conn)) {
                self::$conn         = null;
            }
        }
        
        // }}}
        // {{{ __clone()
      
        /**
         * According to singleton instance, cloning is prohibited
         * 
         * @access   private
         * @category cloning
         * @return   void  Nothing as it only shows a warning when tried to clone
         */
        private function __clone()
        {
            /*
             * only set an error message
             */
            die('Cloning is prohibited for singleton instance.');
        }
        // }}}
    }
}
