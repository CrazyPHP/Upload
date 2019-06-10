<?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfoInterface;
use Upload\ValidationInterface;

/**
 * Class Extension
 *
 * @package Upload\Validation
 */
class Extension implements ValidationInterface
{
    /**
     * Array of acceptable file extensions without leading dots
     *
     * @var array
     */
    protected $allowedExtensions;

    /**
     * @param string|array $allowedExtensions Allowed file extensions
     * @example new \Upload\Validation\Extension(['png','jpg','gif'])
     * @example new \Upload\Validation\Extension('png')
     */
    public function __construct($allowedExtensions)
    {
        if (is_string($allowedExtensions) === true) {
            $allowedExtensions = array($allowedExtensions);
        }

        $this->allowedExtensions = array_map('strtolower', $allowedExtensions);
    }

    /**
     * @param FileInfoInterface $fileInfo
     * @throws Exception If validation fails
     */
    public function validate(FileInfoInterface $fileInfo)
    {
        $fileExtension = strtolower($fileInfo->getExtension());

        if (in_array($fileExtension, $this->allowedExtensions) === false) {
            throw new Exception(sprintf('Invalid file extension. Must be one of: %s', implode(', ', $this->allowedExtensions)), $fileInfo);
        }
    }
}
