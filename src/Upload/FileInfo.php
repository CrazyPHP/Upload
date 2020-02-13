<?php

namespace Upload;

/**
 * Class FileInfo
 * @package Upload
 */
class FileInfo extends \SplFileInfo implements FileInfoInterface
{
    /**
     * File name (without extension)
     * @var string
     */
    protected $name;

    /**
     * File extension (without dot prefix)
     * @var string
     */
    protected $extension;

    /**
     * File mimetype
     * @var string
     */
    protected $mimetype;

    /**
     * @param string $filePathname Absolute path to uploaded file on disk
     * @param string $newName Desired file name (with extension) of uploaded file
     */
    public function __construct($filePathname, $newName = null)
    {
        $desiredName = is_null($newName) ? $filePathname : $newName;
        $this->setName(pathinfo($desiredName, PATHINFO_FILENAME));
        $this->setExtension(pathinfo($desiredName, PATHINFO_EXTENSION));

        parent::__construct($filePathname);
    }

    /**
     * Get file name (without extension)
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set file name (without extension)
     * It also makes sure file name is safe
     * @param string $name
     * @return FileInfo Self
     */
    public function setName($name)
    {
        $name = preg_replace("/([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})/", "", $name);
        $name = basename($name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get file extension (without dot prefix)
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set file extension (without dot prefix)
     * @param string $extension
     * @return FileInfo Self
     */
    public function setExtension($extension)
    {
        $this->extension = strtolower($extension);

        return $this;
    }

    /**
     * Get file name with extension
     * @return string
     */
    public function getNameWithExtension()
    {
        return $this->extension === '' ? $this->name : sprintf('%s.%s', $this->name, $this->extension);
    }

    /**
     * Get mimetype
     * @return string
     */
    public function getMimetype()
    {
        if (isset($this->mimetype) === false) {
            $finfo = new \finfo(FILEINFO_MIME);
            $mimetype = $finfo->file($this->getPathname());
            $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
            $this->mimetype = strtolower($mimetypeParts[0]);
            unset($finfo);
        }

        return $this->mimetype;
    }

    /**
     * Get a specified hash
     * @param string $algorithm md5|sha256
     * @return string
     */
    public function getHash($algorithm = 'sha256')
    {
        return hash_file($algorithm, $this->getPathname());
    }

    /**
     * Get image dimensions
     * @return array formatted array of dimensions
     */
    public function getDimensions()
    {
        list($width, $height) = getimagesize($this->getPathname());

        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Is this file uploaded with a POST request?
     * @return bool
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->getPathname());
    }
}
