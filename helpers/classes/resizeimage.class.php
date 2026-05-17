<?php

/**
 * Resize image class will allow you to resize an image
 *
 * Can resize to exact size
 * Max width size while keep aspect ratio
 * Max height size while keep aspect ratio
 * Automatic while keep aspect ratio
 */
class ResizeImage {

    private $ext;
    private $image;
    private $newImage;
    private $origWidth;
    private $origHeight;
    private $resizeWidth;
    private $resizeHeight;
    /**
     * To store class instance object
     * 
     * @access private
     * @var    object  The current class singleton object
     * @static
     */
    private static $_classObject;

    /**
     * Class constructor requires to send through the image filename
     *
     * @param string $filename - Filename of the image you want to resize
     */
    private function __construct() {
        $this->image = null;
        $this->newImage = null;
        $this->origHeight = 0;
        $this->origWidth = 0;
        $this->resizeHeight = 0;
        $this->resizeWidth = 0;
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
    /**
     * Set the image variable by using image create
     *
     * @param string $filename - The image filename
     */
    public function setImage($filename) {
        if (!file_exists($filename)) {
            throw new Exception('Image ' . $filename . ' can not be found, try another image.');
        }
        $size = getimagesize($filename);
        $this->ext = $size['mime'];

        switch ($this->ext) {
            // Image is a JPG
            case 'image/jpg':
            case 'image/jpeg':
                // create a jpeg extension
                $this->image = imagecreatefromjpeg($filename);
                break;

            // Image is a GIF
            case 'image/gif':
                $this->image = @imagecreatefromgif($filename);
                break;

            // Image is a PNG
            case 'image/png':
                $this->image = @imagecreatefrompng($filename);
                break;

            // Mime type not found
            default:
                throw new Exception("File is not an image, please use another file type.", 1);
        }

        $this->origWidth = imagesx($this->image);
        $this->origHeight = imagesy($this->image);
    }

    /**
     * Save the image as the image type the original image was
     *
     * @param  String[type] $savePath     - The path to store the new image
     * @param  string $imageQuality 	  - The qulaity level of image to create
     *
     * @return Saves the image
     */
    public function saveImage($savePath, $imageQuality = "100", $download = false) {
        switch ($this->ext) {
            case 'image/jpg':
            case 'image/jpeg':
                // Check PHP supports this file type
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->newImage, $savePath, $imageQuality);
                }
                break;

            case 'image/gif':
                // Check PHP supports this file type
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->newImage, $savePath);
                }
                break;

            case 'image/png':
                $invertScaleQuality = 9 - round(($imageQuality / 100) * 9);

                // Check PHP supports this file type
                if (imagetypes() & IMG_PNG) {
                    imagepng($this->newImage, $savePath, $invertScaleQuality);
                }
                break;
        }

        if ($download) {
            header('Content-Description: File Transfer');
            header("Content-type: application/octet-stream");
            header("Content-disposition: attachment; filename= " . $savePath . "");
            readfile($savePath);
        }

        imagedestroy($this->newImage);
    }

    /**
     * Resize the image to these set dimensions
     *
     * @param  int $width        	- Max width of the image
     * @param  int $height       	- Max height of the image
     * @param  string $resizeOption - Scale option for the image
     *
     * @return Save new image
     */
    public function resizeTo($width, $height, $resizeOption = 'default') {
        switch (strtolower($resizeOption)) {
            case 'exact':
                $this->resizeWidth = $width;
                $this->resizeHeight = $height;
                break;

            case 'maxwidth':
                $this->resizeWidth = $width;
                $this->resizeHeight = $this->resizeHeightByWidth($width);
                break;

            case 'maxheight':
                $this->resizeWidth = $this->resizeWidthByHeight($height);
                $this->resizeHeight = $height;
                break;

            default:
                if ($this->origWidth > $width || $this->origHeight > $height) {
                    if ($this->origWidth > $this->origHeight) {
                        $this->resizeHeight = $this->resizeHeightByWidth($width);
                        $this->resizeWidth = $width;
                    } else if ($this->origWidth < $this->origHeight) {
                        $this->resizeWidth = $this->resizeWidthByHeight($height);
                        $this->resizeHeight = $height;
                    } else {
                        $this->resizeWidth = $width;
                        $this->resizeHeight = $height;
                    }
                } else {
                    $this->resizeWidth = $width;
                    $this->resizeHeight = $height;
                }
                break;
        }

        $this->newImage = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
        imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->origWidth, $this->origHeight);
    }

    /**
     * Get the resized height from the width keeping the aspect ratio
     *
     * @param  int $width - Max image width
     *
     * @return Height keeping aspect ratio
     */
    private function resizeHeightByWidth($width) {
        return floor(($this->origHeight / $this->origWidth) * $width);
    }

    /**
     * Get the resized width from the height keeping the aspect ratio
     *
     * @param  int $height - Max image height
     *
     * @return Width keeping aspect ratio
     */
    private function resizeWidthByHeight($height) {
        return floor(($this->origWidth / $this->origHeight) * $height);
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
        // check if image object is present and destroy it
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
        if (is_resource($this->newImage)) {
            imagedestroy($this->newImage);
        }
    }

    // }}}
    // {{{ __clone()

    /**
     * According to singleton instance, cloning is prihibited
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
