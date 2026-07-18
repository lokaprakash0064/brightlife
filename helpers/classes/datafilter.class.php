<?php

/**
 * Class file to clean user supplied data and
 * encrypt check for password etc
 * 
 * PHP version 5.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ CodeCanyon
 *
 * @category   DataFilter
 * @package    TBF
 * @author     Kirti Kumar Nayak <admin@thebestfreelancer.in>
 * @copyright  2015 The Best Freelancer
 * @license    http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ CodeCanyon
 * @version    GIT: $Id$
 * @link       http://repo.thebestfreelancer.in
 * @since      File available since Release 1.0
 * @deprecated File deprecated in Release 1.0
 */

// Check if DataFilter class exists or not and define if not
if (!class_exists('DataFilter')) {

    /**
     * Class file to filter user requests or data submitted
     * 
     * PHP version 5.5
     *
     * @category  DataFilter
     * @package   TBF
     * @author    Kirti Kumar Nayak <admin@thebestfreelancer.in>
     * @copyright 2015 The Best Freelancer
     * @license   http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ CodeCanyon
     * @version   Release: 2.0
     * @link      http://repo.thebestfreelancer.in
     * @since     Class available since Release 1.0
     */
    class DataFilter
    {
        // {{{ properties

        /**
         * Private variable for HTML string replaces
         * 
         * @access private
         * @var mixed The replaces for HTML string
         */
        private $_replaces;

        /**
         * Private variable to store any string
         * mainly used to store user submitted data
         * 
         * @access private
         * @var    string  The string submitted by user/visitor
         */
        private $_str;

        /**
         * Private variable to store a string of allowable HTML tags
         * to be used against code / CSFR / XSS attacks by visitors/hackers
         * when FILTER_SELECTIVELY is used
         * 
         * @access private
         * @var    string  The allowable html tags for user
         */
        private $_allowedHTML;

        /**
         * Private variable to store
         * an array of restricted HTML/special characters
         * to be used against code / CSFR / XSS attacks by visitors/hackers
         * 
         * @access private
         * @var    string  The restricted special characters/strings for user
         */
        private $_restrictedChars;

        /**
         * Private variable to store
         * option to filter the data
         * Possible values: FILTER_SELECTIVELY / FILTER_SECURELY
         * 
         * @access private
         * @var    string  The option of filter type
         */
        private $_filterType;

        /**
         * Private variable to store
         * filtered data array submitted by user
         * 
         * @access private
         * @var    string  The filtered user submitted data
         */
        private $_cleanData;

        /**
         * Private variable to store
         * html translation table to relace special characters if any
         * to be used for secure filtration (FILTER_SECURELY)
         * 
         * @access private
         * @var    string  The option of filter type
         */
        private $_htmlTransTable;

        /**
         * Private static variable to hold singleton class object
         * 
         * @access    private
         * @staticvar
         * @var       object  The current class object
         */
        private static $_classObject;

        // }}}
        // {{{ __construct()

        /**
         * Default constructor class to initialize variables and page data.
         * Accoring to singleton class costructor must be private
         * 
         * @return void
         * @access private
         */
        private function __construct() 
        {
            /*
             * Initialize the string replaces to be used in HTML script
             * to compress the HTML output
             */
            $this->_replaces = array("\n", "\r\n", "\r", "\t", '  ', '   ');
            /*
             * Initialize the allowed html tags which user/visitor can
             * use to send a html formatted message
             * define more if you want
             */
            $this->_allowedHTML = '<a><br><div><p><span><strong>';
            $this->_allowedHTML .= '<h1><h2><h3><h4><h5><h6><hr>';
            $this->_allowedHTML .= '<table><tr><td><th><thead><tfoot>';
            /*
             * Initialize the allowed html tags which user/visitor can
             * use to send a html formatted message
             * define more if you want
             */
            $this->_restrictedChars = array('"', 'javascript', '()', '\\');
            /*
             * initialize the filter option as FILTER_SELECTIVELY
             * as many users may want to use html tags
             */
            $this->_filterType = 'FILTER_SELECTIVELY';
            /*
             * initialize html special characters to be replaced
             * when FILTER_SECURELY is used
             */
            $this->_htmlTransTable = get_html_translation_table(HTML_ENTITIES);
            // Initialize clean data container var as null
            $this->_cleanData = array();
        }

        // }}}
        // {{{ getObject()

        /**
         * Method to return singleton class object.
         * returns current class object if already present
         * else creates one
         * 
         * @return object  The current class object
         * @access public
         * @static
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
        // {{{ cleanData()

        /**
         * The following method makes a variable safe
         * as that may contain unacceptable formats or data
         * to prevent security holes those may be a threat
         * 
         * @param mixed  $submittedData The data submitted by the user to be filtered
         * @param string $filterOption  The data filter mode set
         * 
         * @return mixed  Cleaned data submitted by the user
         * @access public
         */
        public function cleanData($submittedData, $filterOption = 'FILTER_SECURELY') 
        {
            try {
                // assign filter option with private propery
                $this->_filterType = $filterOption;
                // check if the data is an array or not
                if (!is_array($submittedData)) {
                    // if that is not an array, treat that as a string
                    $this->_str = $submittedData;
                    // trim the spaces if any
                    $this->_str = trim($this->_str);
                    // remove slashes
                    $this->_str = stripslashes($this->_str);
                    /*
                     * check if selective filter has been opted, if true
                     * escape the data and insert null where restricted characters found
                     * allow the tags for user and strip off rest of them
                     */
                    if ($this->_filterType === 'FILTER_SELECTIVELY') {
                        $this->_str = str_ireplace($this->_restrictedChars, "", $this->_str);
                        $this->_str = strip_tags($this->_str, $this->_allowedHTML);
                    } else {
                        // refer to html replacements to replace tags which may lead to injection
                        $this->_str = strtr($this->_str, $this->_htmlTransTable);
                    }
                    // now return the cleaned data
                    return $this->_str;
                } else {
                    /**
                     * Var to keep cleaned data array for a temporary period
                     * so that they can be returned in cleaned state
                     * and acceptable format
                     * 
                     * @var    mixed  The injection cleaned data array
                     * @access private
                     */
                    $cleanedData = array();
                    /*
                     * if the data is an array
                     * fetch the array values one by one by the loop
                     */
                    foreach ($submittedData as $pointer => $str) {
                        // Recursively call clean function if the data is array
                        $cleanedData[$pointer] = $this->cleanData($str, $this->_filterType);
                    }
                    // return the cleaned data array
                    return $cleanedData;
                }
            } catch (Exception $ex) {
                // Catch any Exceptions occured
                application_error($ex, 'Data cleaning failed');
            }
        }

        // }}}
        // {{{ pwdHash()

        /**
         * This mehod encrypts passwords into different string which can't be
         * reversed. Either password can be reset else deleted
         * 
         * @param string $str The string to be encrypted
         * 
         * @return string The hashed string from a passed string
         * @access public
         */
        public function pwdHash($str) 
        {
            $salt = SECURITY_SALT;
            $converted_input = $str;
            // iterate 5 times to encrypt the string
            for ($i = 0; $i < 5; ++$i) {
                // taking sha256 hashing algorithm
                $converted_input = hash('sha256' , $converted_input);
                // hashing the salt defined in include.php by sha1
                $salt = sha1($salt);
            }
            // join these two by again converting both the salt and input string by md5
            $encrypted = md5($salt . $converted_input);
            // now return the encypted data altogether
            return $encrypted;
        }
        
        // }}}
        // {{{ uniqueId()

        /**
         * This mehod can be used to get unique ids
         * 
         * @return string The hashed string
         * @access public
         */
        public function uniqueId() 
        {
            // get microtime converted to password
            $str = $this->pwdHash(microtime());
            $resultChars = '';
            /*
             * create a loop to get random 20 characters from the character set
             * then md5 that and take 5 characters from start
             */
            while (strlen($resultChars) < 20) {
                /*
                 * get the character randomly
                 * by selecting between 0 to length of the charSet
                 */
                $charIndex              =    rand(0, strlen($str));
                $char                   =    substr($str, $charIndex, 1);
                $resultChars            .=   $char;
            }
            // now return the encypted data altogether
            return $resultChars;
        }

        // }}}
        // {{{ __clone()

        /**
         * According to singleton pattern instance, cloning is prihibited
         *
         * @return string  A message that states, cloning is prohibited
         * @access public
         */
        private function __clone() 
        {
            // only set an error message
            die('Cloning is prohibited for singleton instance.');
        }

        // }}}
    }
}
