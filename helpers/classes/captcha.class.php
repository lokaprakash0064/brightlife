<?php

/**
 * Class file to handle captcha image requests
 *
 * Used PDO class to automate and faster access.
 * 
 * PHP version 5.5
 *
 * LICENSE: This source file is subject to the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://codecanyon.net/wiki/support/legal-terms/licensing-terms/
 *
 * @category   Security
 * @package    TBF
 * @author     Kirti Kumar Nayak <admin@thebestfreelancer.in>
 * @copyright  2015 The Best Freelancer
 * @license    http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ CodeCanyon
 * @version    GIT: 2.0
 * @link       http://repo.thebestfreelancer.in
 * @since      File available since Release 1.0
 * @deprecated File deprecated in Release 1.0
 */

// Check if Captcha class exists or not and define if not
if (!class_exists('Captcha')) {
    
    /**
     * Class file to handle captcha image requests
     * 
     * This is a singleton pattern class and can be called via static methods
     * 
     * @category   Security
     * @package    TBF
     * @author     Kirti Kumar Nayak <admin@thebestfreelancer.in>
     * @copyright  2015 The Best Freelancer
     * @license    http://codecanyon.net/wiki/support/legal-terms/licensing-terms/ CodeCanyon
     * @version    Release: 2.0
     * @link       http://repo.thebestfreelancer.in
     * @since      Class available since Release 1.0
     * @deprecated Class deprecated in Release 1.0
     */
    class Captcha
    {
        // {{{ properties
        
        /**
         * Private variable for random characters used by captcha method
         * 
         * @access private
         * @var    string  The character set from which captcha should be generated
         */
        private $_charSet;
        
        /**
         * Private variable to store captcha type
         * if you have no GD library installed you may switch over to
         * Javascript captcha
         * 
         * @access private
         * @var    string  The type of the captcha wanted
         */
        private $_captchaType;
        
        /**
         * Private variable to store captcha width
         * 
         * @access private
         * @var    int  The height of the captcha image
         */
        private $_captchaWidth;
        
        /**
         * Private variable to store the captcha height
         * 
         * @access private
         * @var    int  The height of the captcha image
         */
        private $_captchaHeight;
        
        /**
         * Protected property to hold the lowercase letter set
         * to be used to generate random string
         * 
         * @access protected
         * @var    string  The lowercase character set string
         */
        protected $lowerCaseChars;
        
        /**
         * Protected property to hold the uppercase letter set
         * to be used to generate random string
         * 
         * @access protected
         * @var    string  The uppercase character set string
         */
        protected $upperCaseChars;
        
        /**
         * Protected property to hold the numeric character set
         * to be used to generate random string
         * 
         * @access protected
         * @var    string  The numeric character set string
         */
        protected $numericChars;
        
        /**
         * Protected property to hold the special character set
         * to be used to generate random string
         * 
         * @access protected
         * @var    string  The special character set string
         */
        protected $specialChars;
        
        /**
         * Private variable to store the location of the font to be used in captcha image
         * 
         * @access private
         * @var    string  The path string of the ttf font file
         */
        private $_captchaFontLocation;
        
        /**
         * Private variable to store the captcha image font size
         * 
         * @access private
         * @var    float  The font size for the captcha image
         */
        private $_captchaFontSize;
        
        /**
         * Private variable to store the captcha string angle
         * 
         * @access private
         * @var    float  The angle of the captcha string
         */
        private $_captchaCharAngle;
        
        /**
         * Private variable to store first number of the math captcha
         * 
         * @access private
         * @var    int The first integer to be stored for math captcha
         */
        private $_firstInt;
        
        /**
         * Private variable to store second number of the math captcha
         * 
         * @access private
         * @var    int The second integer for the math captcha
         */
        private $_secondInt;
        
        /**
         * Private static variable to hold class object
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
            // captcha configurations
            
            /*
             * define captcha type as php
             * you may use javascript captcha too
             * possible values: php, js
             */
            $this->_captchaType         =   'php';
            /*
             * initialize captcha image width
             * it is defined as per the html design
             */
            $this->_captchaWidth        =   70;            
            /*
             * initialize the captcha image height
             * defined optimum for the design
             */
            $this->_captchaHeight       =   30;            
            /*
             * initialize the font file location to be used for captcha characters
             * it must be a valid ttf font file at the specified location
             */
            $this->_captchaFontLocation = dirname(dirname(__FILE__))
                                            . DIRECTORY_SEPARATOR
                                            . 'fonts'
                                            . DIRECTORY_SEPARATOR
                                            .'MONOFONT.TTF';
            /*
             * initialize the font size of the captcha string
             * by default the maximum defined i.e. 80% of the image height
             */
            $this->_captchaFontSize     = $this->_captchaHeight * 0.8;
            /*
             * initialize the lowercase character set from a-z
             * Intentionally excluded 0, 'o' and 'O' to minimize
             * user ambiguity
             */
            $this->_lowerCaseChars  =    'abcdefghijklmpqrstuvwxyz';
            // initialize the uppercase character set from A-Z
            $this->_upperCaseChars  =    'ABCDEFGHIJKLMNPQRSTUVWXYZ';
            // initalize the numeric character set from 1-9
            $this->_numericChars    =    '123456789';
            // initialize the special character set (add more or delete if you like)
            $this->_specialChars    =    '!$%^&*+#~/|';
            /*
             * initialize the characters angle for the captcha
             * it is randomly set between -2 and 2
             * as the image height and font size are set
             */
            $this->_captchaCharAngle    = rand(-2, 2);
            // initialize the captcha characters as null
            $this->_charSet             =     '';
            // initialize the integers to be used for math captcha as 0
            $this->_firstInt            =   0;
            $this->_secondInt       =   0;
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
        // {{{ _getRandomChars
        
        /**
         * Generate string of random characters
         *
         * @param int  $length         Length of the string to generate
         * @param bool $lowerCaseChars Include lower case characters
         * @param bool $upperCaseChars Include uppercase characters
         * @param bool $numericChars   Include numbers
         * @param bool $specialChars   Include special characters
         * 
         * @access private
         * @return string  The random character string
         */
        private function _getRandomChars(
            $length = 5,
            $lowerCaseChars = true,
            $upperCaseChars = true,
            $numericChars = true,
            $specialChars = false
        ) {
           
            /**
             * Variable to store a random character index every time
             * 
             * @access private
             * @var    int  The random character index out of character set
             */
            $charIndex                  =    '';
           
            /**
             * Variable to store a random character every time
             * 
             * @access private
             * @var    char  The random character out of character set
             */
            $char                       =    '';
           
            /**
             * Variable to store a random character set every time
             * 
             * @access private
             * @var    int  The random character setof length 5 out of character set
             */
            $resultChars                =    '';
           
            /*
             * check if user has opted for lowercase characters
             * if true, then add it to the character set
             */
            if ($lowerCaseChars === true) {
                $this->_charSet         .=   $this->_lowerCaseChars;
            }
            /*
             * Check if user has opted for uppercase characters
             * If true, add it to the character set
             */
            if ($upperCaseChars === true) {
                $this->_charSet         .=   $this->_upperCaseChars;
            }
            /*
             * Check if user has opted for numeric characters
             * If true, add it to the character set
             */
            if ($numericChars === true) {
                $this->_charSet         .=   $this->_numericChars;
            }
            /*
             * Check if user has opted for uppercase characters
             * If true, add it to the character set
             */
            if ($specialChars === true) {
                $this->_charSet         .=   $this->_specialChars;
            }
            
            // Check if length has given greater than 0 else return null
            if (($length < 1) || ($length == 0)) {
                return $resultChars;
            }

            /*
             * create a loop to get random 5 characters from the character set
             * then md5 that and take 5 characters from start
             */
            while (strlen($resultChars) < $length) {
                /*
                 * get the character randomly
                 * by selecting between 0 to length of the charSet
                 */
                $charIndex              =    rand(0, strlen($this->_charSet));
                $char                   =    substr($this->_charSet, $charIndex, 1);
                $resultChars            .=   $char;
            }
            // at last return the result chars
            return $resultChars;
        }
        
        // }}}
        // {{{ imageCaptcha()
        
        /**
         * Method to create captcha image for bot verification
         *
         * @return mixed  Image for captcha
         * @throws Exception  GD or general exceptions
         * @access public
         */
        public function imageCaptcha()
        {
            try {
                // Assign the characters to a session variable
                $_SESSION['CaptchaChars']    =    $this->_getRandomChars(5, true, true, true, false);
                // Close the session write buffer to avoid overwriting
                session_write_close();
                // Create a 100 X 30 image and assign it to a var
                $img                        =     imagecreatetruecolor($this->_captchaWidth, $this->_captchaHeight);
                // create a white color
                $white                      =    imagecolorallocate($img, 255, 255, 255);
                // Create a random color to write the characters prominently
                $color                      =    imagecolorallocate($img, rand(0,155), rand(0,155), rand(0,155));
                // fill the rectangular image with white background
                imagefilledrectangle($img, 0, 0, 399, 30, $white);
                // Write the string inside the image with black color
                imagettftext(
                    $img,
                    $this->_captchaFontSize,
                    $this->_captchaCharAngle,
                    2,
                    25,
                    $color,
                    $this->_captchaFontLocation,
                    $_SESSION['CaptchaChars']
                );
                /*
                 * generating dots randomly in background
                 * to make an image noise
                 * if you want more noise replace the argument 5
                 * as per your requirement
                 */ 
                for ( $i=0; $i<10; $i++ ) {
                    imagefilledellipse(
                        $img,
                        mt_rand(0,  $this->_captchaWidth),
                        mt_rand(0,  $this->_captchaHeight),
                        2,
                        3,
                        0
                    );
                }
                /*
                 * generating lines randomly in background of image
                 * for more noise
                 * if you want more noise replace the argument 10
                 * as per your requirement
                 */ 
                for ( $i=0; $i<20; $i++ ) {
                    imageline(
                        $img,
                        mt_rand(0, $this->_captchaWidth),
                        mt_rand(0, $this->_captchaHeight),
                        mt_rand(0, $this->_captchaWidth),
                        mt_rand(0, $this->_captchaHeight),
                        $color
                    );
                }
                // Output the image
                header('Content-Type: image/gif');
                // output a gif image
                imagegif($img);
                // destroy the image to save server space
                imagedestroy($img);
            }
            catch(Exception $ex) {
                die('Oh no.. Something gone wrong... Details: ' . $ex->getMessage());
            }
        }
        
        // }}}
        // {{{ _mathCaptcha
        
        /**
         * Method to create text based math captcha
         * for bot verification especially those do not
         * have GD library in their server
         *
         * @return string  A math based question
         * @access public
         */
        public function mathCaptcha()
        {
            /*
             * take two variables with random integer values,
             * then add them and save in session to verify
             */
            $this->_firstInt            =   rand(1, 9);
            $this->_secondInt           =   rand(1, 9);
            // Assign the characters to a session variable
            $_SESSION['CaptchaChars']    =   $this->_firstInt + $this->_secondInt;
            // Close the session write buffer to avoid overwriting
            session_write_close();
            // simply return the characters string
            echo '<h4 class="no-margin">'. $this->_firstInt .' + '. $this->_secondInt .' =</h4>';
        }
        
        // }}}
        // {{{ createCaptcha()
        
        /**
         * Public method to respond captcha requests
         * which checks if server has PHP GD library extension
         * else generates a text based captcha
         * 
         * @return void outputs standard string
         * @access public
         */
        public function createCaptcha()
        {
            /*
             * check if server is loaded with GD library extension
             * if true call the image based captcha method
             * else call the text based captcha method
             */
            if (extension_loaded('gd') and ($this->_captchaType==='php')) {
                $this->imageCaptcha();
            } else {
                $this->mathCaptcha();
            }
        }
        
        // }}}
        // {{{ __clone()
        
        /**
         * According to singleton pattern instance, cloning is prihibited
         *
         * @return string  A message that states, cloning is prohibited
         * @access private
         */
        private function __clone()
        {
            // only set an error message
            die('Cloning is prohibited for singleton instance.');
        }
        
        // }}}
        
    }
}
