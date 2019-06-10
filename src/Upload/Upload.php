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
     * @var FileInfoInterface[]
     */
    protected $objects = [];

    /**
     * Validations
     *
     * @var ValidationInterface[]
     */
    protected $validations = [];

    /**
     * Upload errors
     *
     * @var array
     */
    protected $uploadErrors = [];

    /**
     * Validate errors
     *
     * @var array
     */
    protected $validateErrors = [];

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
                        $this->uploadErrors[] = sprintf(
                            '%s: %s',
                            $file['name'][$index],
                            static::$errorCodeMessages[$file['error'][$index]]
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
                        static::$errorCodeMessages[$file['error']]
                    );
                } else {
                    $this->objects[] = new FileInfo($file['tmp_name'], $file['name']);
                }
            }
        }

        $this->storage = $storage;
    }

    /**
     * Add file validations
     *
     * @param ValidationInterface[] $validations
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
     * @return ValidationInterface[]
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Is all files valid or not?
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->objects as $fileInfo) {
            foreach ($this->validations as $validation) {
                try {
                    $validation->validate($fileInfo);
                } catch (Exception $e) {
                    $text = sprintf(
                        '%s: %s',
                        $fileInfo->getNameWithExtension(),
                        $e->getMessage()
                    );
                    $fileInfo->addError($text);
                    $this->validateErrors[] = $text;
                }
            }
        }

        return empty($this->validateErrors);
    }

    /**
     * Is all files uploaded or not?
     *
     * @return bool
     */
    public function isUploaded(){
        return empty($this->uploadErrors);
    }

    /**
     * Get file upload errors
     *
     * @return array
     */
    public function getUploadErrors()
    {
        return $this->uploadErrors;
    }

    /**
     * Get file validation errors
     *
     * @return array
     */
    public function getValidateErrors()
    {
        return $this->validateErrors;
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
     *
     * @return bool
     *
     * @throws Exception If upload/validation fails
     */
    public function store()
    {
        if ($this->isUploaded() == false || $this->isValid() == false) {
            throw new Exception('File upload/validation failed');
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
