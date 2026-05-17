<?php

/**
 * Check if Contact class exists or not and define if not
 */
if (!class_exists('Uploader')) {
    
    /**
     * Class file to filter user requests or data submitted
     * PHP version >= 5.3
     *
     * @category  Uploader
     * @package   DocumentUploader
     * @author    Kirti Kumar Nayak <admin@thebestfreelancer.in>
     * @copyright (c) 2015, The Best Freelancer,
     * @license   http://thebestfreelancer.in The Best Freelancer
     * @version   Build 1.0
     */

    class Uploader
    {
        // {{{ properties
        
        /**
         * Stores errors at the time of uploading
         * 
         * @access private
         * @var mixed The error encountered at the time of upload
         */
        private $_uploadErrors;
        /**
         * stores allowed file types
         * 
         * @access public
         * @var mixed The types of file allowed to be uploaded
         */
        public $allowedFileTypes;
        /**
         * Stores allowed file size
         * 
         * @access public
         * @var mixed The size of file allowed to be uploaded
         */
        public $allowedFileSize;
        /**
         * stores file type
         * 
         * @access private
         * @var string The type of files supplied in an array
         */
        private $_fileType;
        /**
         * Stores file name
         * 
         * @access private
         * @var string The name of the file supplied
         */
        private $_fileName;
        /**
         * Stores file size
         * 
         * @access private
         * @var double The size of the file supplied
         */
        private $_fileSize;
        /**
         * Stores submitted data
         * 
         * @access private
         * @var mixed The submitted details of file
         */
        private $_submittedFileData;
        /**
         * Stores new file name to rename a file
         * 
         * @access private
         * @var string The new filename
         */
        private $_newFileName;
        /**
         * Stores file details like name, new name, size etc
         * 
         * @access private
         * @var mixed The details of the uploaded file(s)
         */
        private $_fileDetails;
        /**
         * Stores file list with details like name, new name, size etc
         * 
         * @access private
         * @var mixed The details of the uploaded file(s)
         */
        private $_fileList;
        /**
         * Stores the directory path where files to be uploaded
         * 
         * @access public
         * @var string The location to store the uploaded file(s)
         */
        public $uploadDir;
        
        /**
         * private static variable to hold class object
         * 
         * @access private
         * @staticvar
         * @var object  The current class object
         */
        private static $_classObject;
        // }}}
        // {{{ __construct()
        
        /**
         * Default constructor class to initialize variables and page data.
         * Accoring to singleton class costructor must be private
         * 
         * @return void
         * @access  private
         */
        private function __construct()
        {
            /*
             * initialize all the variables
             */
            $this->_uploadErrors        = array();
            // allow 2MB max to upload
            $this->allowedFileSize      = 2 * 1024 * 1024;
            $this->allowedFileTypes     = ['pdf', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'];
            $this->_fileName            = '';
            $this->_fileSize            = 0;
            $this->_fileType             = '';
            $this->_submittedFileData   = array();
            $this->_newFileName         = '';
            $this->_fileDetails         = array();
            $this->_fileList            = array();
            $this->uploadDir            = UPLOAD_DIR;
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
         * 
         */
        public static function getObject()
        {
            /*
             *  check if class not instantiated
             */
            if (self::$_classObject === null) {
                /*
                 *  then create a new instance
                 */
                self::$_classObject = new self();
            }
            /*
             *  return the class object to be used
             */
            return self::$_classObject;
        }
        
        // }}}
        // {{{ getMaxUploadLimit()
        
        /**
         * Method to get max. upload limit of the server in order to
         * make sure that the file gets uploaded
         * 
         * @access public
         * @return integer The file max. file upload limit in bytes
         */
        public function getMaxUploadLimit()
        {
            // get max upload limit from config file
            $maxServerUploadLimit = ini_get('post_max_size');
            // remove whitespaces
            $maxServerUploadLimit = trim($maxServerUploadLimit);
            // get last character and convert to lowercase
            $last = strtolower($maxServerUploadLimit[strlen($maxServerUploadLimit)-1]);
            // get max upload to string
            $maxServerUploadLimit = substr($maxServerUploadLimit, 0, (strlen($maxServerUploadLimit) -1));
            // switch to the case and multiply by 1024
            switch($last) {
                case 'P':  
                    $maxServerUploadLimit *= 1024;  
                case 'T':  
                    $maxServerUploadLimit *= 1024;  
                case 'g':
                    $maxServerUploadLimit *= 1024;
                case 'm':
                    $maxServerUploadLimit *= 1024;
                case 'k':
                    $maxServerUploadLimit *= 1024;
            }
            return $maxServerUploadLimit;
        }
        
        // }}}
        // {{{ doUpload()
        
        /**
         * Method to start and do all uploading of files etc
         * have options to set if to rename the file or retain the
         * original name of the file
         * 
         * @param mixed $globalFileData The gloabal data of the file in $_FILES
         * @param string $uploadDir The directory to save the uploaded files
         * @param boolean $renameOption The option to rename or not the file
         * 
         * @access public
         * @return boolean The file uploaded or not
         */
        public function doUpload($globalFIleData, $uploadDir = '', $renameOption = true)
        {
            $this->_submittedFileData = $globalFIleData;
            $this->uploadDir = (empty($uploadDir) ? $this->uploadDir : $uploadDir);
            $this->_uploadErrors = array();
             // check if the target directory present else create new
            if (!is_dir($this->uploadDir)) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = 'Is the'
                    . ' upload directory present ??';
                return $this->_uploadErrors;
            }
            // check if the directory writable
            if (!is_writable($this->uploadDir)) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = 'Is the'
                    . ' upload directory writable ??';
                return $this->_uploadErrors;
            }
            // check if the file name has been set
            if (empty($this->_submittedFileData['name'])) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = 'No Name'
                    . ' found for file selected';
                return $this->_uploadErrors;
            }
            
            // check if the file size is acceptable
            if (intval($this->_submittedFileData['size']) > $this->getMaxUploadLimit()) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = 'File Exceeds'
                    . ' the max server limit';
                return $this->_uploadErrors;
            }
            // store file name and size
            $this->_fileName            = $this->_submittedFileData['name'];
            $this->_fileSize            = intval($this->_submittedFileData['size']);

            // get the file extension by exploding from '.'
            $temp                       = explode('.', $this->_fileName);
            $this->_fileType            = array_pop($temp);

            // check if the file type is allowed
            if (!in_array(strtolower($this->_fileType), $this->allowedFileTypes)) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = $this->_fileType
                    . ' - type of file is not allowed. Allowed file types are: ' . implode(', ', $this->allowedFileTypes);
                return $this->_uploadErrors;
            }
            // check if the file is executable or not
            if (is_executable($this->_fileName)) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = 'Executable files'
                    . ' can\'t be uploaded';
                return $this->_uploadErrors;
            }
            // check if the filesize is more than allowed
            if ($this->_fileSize > $this->allowedFileSize) {
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = 'The file you uploaded ('
                    . $this->_fileName
                    . ') was too large than allowed limit';
                return $this->_uploadErrors;
            }
            // check if renaming has been opted then rename 
            // the file with its filename and time appended md5 value
            if ($renameOption) {
                $this->_newFileName     = md5($this->_fileName . time()) . '.' . $this->_fileType;
            } else {
                $this->_newFileName     = $this->_fileName;
            }
             // check if there is any error while file being attached or uploaded
            if ($this->_submittedFileData['error'] !== UPLOAD_ERR_OK) {
                $this->_uploadErrors['status'] = 'error';
                switch ($this->_submittedFileData['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $this->_uploadErrors['message'] = 'The uploaded file exceeds the maximum file size limit of server';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->_uploadErrors['message'] = 'The uploaded file exceeds the maximum file size limit of the HTML form';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->_uploadErrors['message'] = 'The file was partially uploaded';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $this->_uploadErrors['message'] = 'No file was uploaded';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $this->_uploadErrors['message'] = 'No Temporary folder inside server';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $this->_uploadErrors['message'] = 'Can not write into the server directory / disk';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $this->_uploadErrors['message'] = 'A server side extension avoids to upload the file';
                        break;
                    default:
                        $this->_uploadErrors['message'] = 'Error Code: ' . $this->_submittedFileData['error'];
                        break;
                }
                return $this->_uploadErrors;
            }
            try {
                // if there are no errors the upload the file
                $status = move_uploaded_file(
                    $this->_submittedFileData['tmp_name'],
                    $this->uploadDir . DIRECTORY_SEPARATOR . $this->_newFileName
                );
                if ($status) {
                    // store all the file details into the array
                    $this->_fileDetails = array(
                        'status'        => 'success',
                        'oldFileName'   => $this->_submittedFileData['name'],
                        'newFileName'   => $this->_newFileName,
                        'filesize'      => $this->_fileSize
                    );
                } else {
                    $this->_uploadErrors['status']  = 'error';
                    $this->_uploadErrors['message']  = $exc->getMessage();
                    return $this->_uploadErrors;
                }
            } catch (Exception $exc) {
                // catch exceptions if any
                $this->_uploadErrors['status']  = 'error';
                $this->_uploadErrors['message']  = $exc->getMessage();
                return $this->_uploadErrors;
            }
            return $this->_fileDetails;
        }


        // }}}
        // {{{ uploadMultipleFiles()
        
        /**
         * Handle file upload by catching the global $_FILES
         * Checks whether the global contains single or multiple files
         * then uploads by storing data into an array
         * 
         * @param mixed $globalFiles The $_FILES global or similar array
         *
         * @return mixed  Array containing renamed file name, real name & status
         * @access public
         */
        public function uploadMultipleFiles($globalFiles, $uploadDir = '', $renameOption = true)
        {
            // check if the submitted data not empty
            if (isset($globalFiles) and (count($globalFiles) > 0)) {
                /**
                 * @var int To store the total size of the uploaded files
                 * @access public
                 */
                $totalSize = isset($this->_fileList['totalSize']) ? intval($this->_fileList['totalSize']) : 0;
                /**
                 * to store the total number of submitted files
                 * @var int The number of files
                 */
                $totalFiles = isset($this->_fileList['totalFiles']) ? intval($this->_fileList['totalFiles']) : 0;
                /**
                 * to store the number of files uploaded
                 * @var int The number of files uploaded
                 */
                $uploadedFiles = isset($this->_fileList['uploadedFiles']) ? intval($this->_fileList['uploadedFiles']) : 0;
                /**
                 * to store total size of uploaded files
                 * @var int The total uploaded files size
                 */
                $uploadedSize = isset($this->_fileList['uploadedSize']) ? intval($this->_fileList['uploadedSize']) : 0;
                // start a counter
                $counter = isset($this->_fileList['fileData']) ? count($this->_fileList['fileData']) : 0;
                // check if the supplied file data is an array
                if (is_array($globalFiles['name'])) {
                    // cycle between the values and create an array suitable to pass into the uploader method
                    foreach ($globalFiles['name'] as $key => $value) {
                        $fileData = array(
                            'name' => $value,
                            'tmp_name' => $globalFiles['tmp_name'][$key],
                            'size' => $globalFiles['size'][$key],
                            'type' => $globalFiles['type'][$key],
                            'error' => $globalFiles['error'][$key],
                        );
                        // save the result in an array for future usage
                        $this->_fileList['fileData'][$counter] = $this->doUpload($fileData, $uploadDir, $renameOption);
                        // check if uploaded successfully by checking the file size of the uploaded file
                        if (isset($this->_fileList['fileData'][$counter]['status']) and ($this->_fileList['fileData'][$counter]['status'] === 'success')) {
                            // increment the uploaded file number by 1
                            $uploadedFiles += 1;
                            // increase the total uploaded file size by the uploaded size
                            $uploadedSize += intval($this->_fileList['fileData'][$counter]['filesize']);
                        }
                        // count total files and size
                        $totalFiles += 1;
                        $totalSize += $globalFiles['size'][$key];
                        // increase counter
                        ++$counter;
                    }
                } else {
                    // save the result in an array for future usage
                    $this->_fileList['fileData'] = $this->doUpload($globalFiles, $uploadDir, $renameOption);
                    if (isset($this->_fileList['fileData']['status']) and ($this->_fileList['fileData']['status'] === 'success')) {
                        // increment the uploaded file number by 1
                        $uploadedFiles += 1;
                        // increase the total uploaded file size by the uploaded size
                        $uploadedSize += intval($this->_fileList['fileData']['filesize']);
                    }
                    $totalFiles += 1;
                    $totalSize += $globalFiles['size'];
                }
                $this->_fileList['totalFiles'] = $totalFiles;
                $this->_fileList['totalSize'] = $totalSize;
                $this->_fileList['uploadedFiles'] = $uploadedFiles;
                $this->_fileList['uploadedFileSize'] = $uploadedSize;
                return $this->_fileList;
            } else {
                die('Please specify the array like global $_FILES');
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
            /*
             * only set an error message
             */
            die('Cloning is prohibited for singleton instance.');
        }
        
        // }}}
        
    }
}