<?php

namespace Upload;

/**
 * Class Upload
 * @package Upload
 */
class Upload
{
    /**
     * Upload error code messages
     * @var array
     */
    protected static $errorCodes = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload'
    ];

    /**
     * Storage delegate
     * @var StorageInterface
     */
    protected $storage;

    /**
     * File information
     * @var FileInfoInterface[]
     */
    protected $objects = [];

    /**
     * Upload errors
     * @var array
     */
    protected $uploadErrors = [];

    /**
     * File can be received: from $_FILES, from disk, from array.
     * @param string|array $data
     * @param StorageInterface $storage The upload delegate instance
     */
    public function __construct($data, StorageInterface $storage)
    {

        $file = null;
        $from_disk = false;

        if (is_array($data)) {
            $file = $data;
        } else {
            if (is_file($data)) {
                $this->objects[] = new FileInfo($data);
                $from_disk = true;
            } else {
                $file = $_FILES[$data];
            }
        }

        if ($file !== null) {
            if (is_array($file['tmp_name'])) {
                foreach ($file['tmp_name'] as $index => $tmpName) {
                    if ($file['error'][$index] !== UPLOAD_ERR_OK) {
                        $this->uploadErrors[] = sprintf(
                            '%s: %s',
                            $file['name'][$index],
                            static::$errorCodes[$file['error'][$index]]
                        );
                        continue;
                    } else {
                        $this->objects[] = new FileInfo($file['tmp_name'][$index], $file['name'][$index]);
                    }
                }
            } else {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $this->uploadErrors[] = sprintf(
                        '%s: %s',
                        $file['name'],
                        static::$errorCodes[$file['error']]
                    );
                } else {
                    $this->objects[] = new FileInfo($file['tmp_name'], $file['name']);
                }
            }
        } elseif (!$from_disk) {
            $this->uploadErrors[] = static::$errorCodes[4];
        }

        $this->storage = $storage;
    }

    /**
     * Is all files uploaded or not?
     * @return bool
     */
    public function isUploaded()
    {
        return empty($this->uploadErrors) && count($this->objects) > 0;
    }

    /**
     * Get file upload errors
     * @return array
     */
    public function getUploadErrors()
    {
        return $this->uploadErrors;
    }

    /**
     * @return FileInfoInterface[]
     */
    public function getFiles()
    {
        return $this->objects;
    }

    /**
     * Store file (delegated to storage object)
     * @return bool
     * @throws Exception if store fails
     */
    public function store()
    {
        if ($this->isUploaded()) {
            foreach ($this->objects as $fileInfo) {
                $this->storage->store($fileInfo);
            }
            return true;
        } else {
            return false;
        }
    }
}
