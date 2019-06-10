<?php

namespace Upload;

/**
 * Class Upload
 *
 * @package Upload
 */
class Upload
{
    /**
     * Upload error code messages
     *
     * @var array
     */
    protected static $errorCodeMessages = [
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
     *
     * @var StorageInterface
     */
    protected $storage;

    /**
     * File information
     *
     * @var array[FileInfoInterface]
     */
    protected $objects = [];

    /**
     * Validations
     *
     * @var array[ValidationInterface]
     */
    protected $validations = [];

    /**
     * Validation errors
     *
     * @var array[String]
     */
    protected $errors = [];

    /**
     * File can be received: from $_FILES, from disk, from array.
     *
     * @param string|array $data
     * @param StorageInterface $storage The upload delegate instance
     */
    public function __construct($data, StorageInterface $storage)
    {

        $file = null;

        if (is_array($data)) {
            $file = $data;
        } else {
            if (is_file($data)) {
                $this->objects[] = new FileInfo($data);
            } else {
                $file = $_FILES[$data];
            }
        }

        if ($file !== null) {
            if (is_array($file['tmp_name'])) {
                foreach ($file['tmp_name'] as $index => $tmpName) {
                    if ($file['error'][$index] !== UPLOAD_ERR_OK) {
                        $this->errors[] = sprintf(
                            '%s: %s',
                            $file['name'][$index],
                            static::$errorCodeMessages[$file['error'][$index]]
                        );
                        continue;
                    }
                    $this->objects[] = new FileInfo($file['tmp_name'][$index], $file['name'][$index]);
                }
            } else {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $this->errors[] = sprintf(
                        '%s: %s',
                        $file['name'],
                        static::$errorCodeMessages[$file['error']]
                    );
                }
                $this->objects[] = new FileInfo($file['tmp_name'], $file['name']);
            }
        }

        $this->storage = $storage;
    }

    /**
     * Add file validations
     *
     * @param array[ValidationInterface] $validations
     *
     * @return Upload Self
     */
    public function addValidations(array $validations)
    {
        foreach ($validations as $validation) {
            $this->addValidation($validation);
        }

        return $this;
    }

    /**
     * Add file validation
     *
     * @param ValidationInterface $validation
     *
     * @return Upload Self
     */
    public function addValidation(ValidationInterface $validation)
    {
        $this->validations[] = $validation;

        return $this;
    }

    /**
     * Get file validations
     *
     * @return array[ValidationInterface]
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Is this collection valid and without errors?
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->objects as $fileInfo) {

            // Check is uploaded file
            if ($fileInfo->isUploadedFile() === false) {
                $this->errors[] = sprintf(
                    '%s: %s',
                    $fileInfo->getNameWithExtension(),
                    'Is not an uploaded file'
                );
                continue;
            }

            // Apply user validations
            foreach ($this->validations as $validation) {
                try {
                    $validation->validate($fileInfo);
                } catch (\Upload\Exception $e) {
                    $this->errors[] = sprintf(
                        '%s: %s',
                        $fileInfo->getNameWithExtension(),
                        $e->getMessage()
                    );
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get file validation errors
     *
     * @return array[String]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array[FileInfoInterface]
     */
    public function getFiles(){
        return $this->objects;
    }

    /**
     * Store file (delegated to storage object)
     *
     * @return bool
     *
     * @throws Exception If validation fails
     * @throws Exception If upload fails
     */
    public function store()
    {
        if ($this->isValid() === false) {
            throw new Exception('File validation failed');
        }

        foreach ($this->objects as $fileInfo) {
            $this->storage->store($fileInfo);
        }

        return true;
    }

    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     *
     * @param string $input
     *
     * @return int
     */
    public static function humanReadableToBytes($input)
    {
        $number = (int)$input;
        $units = array(
            'b' => 1,
            'k' => 1024,
            'm' => 1048576,
            'g' => 1073741824
        );
        $unit = strtolower(substr($input, -1));
        if (isset($units[$unit])) {
            $number = $number * $units[$unit];
        }

        return $number;
    }
}
