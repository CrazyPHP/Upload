<?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfoInterface;
use Upload\ValidationInterface;

/**
 * Class Mimetype
 *
 * @package Upload\Validation
 */
class Mimetype implements ValidationInterface
{
    /**
     * Valid media types
     *
     * @var array
     */
    protected $mimetypes;

    /**
     * @param string|array $mimetypes
     */
    public function __construct($mimetypes)
    {
        if (is_string($mimetypes) === true) {
            $mimetypes = array($mimetypes);
        }
        $this->mimetypes = $mimetypes;
    }

    /**
     * @param FileInfoInterface $fileInfo
     *
     * @throws Exception If validation fails
     */
    public function validate(FileInfoInterface $fileInfo)
    {
        if (in_array($fileInfo->getMimetype(), $this->mimetypes) === false) {
            throw new Exception(sprintf('Invalid mimetype. Must be one of: %s', implode(', ', $this->mimetypes)), $fileInfo);
        }
    }
}
