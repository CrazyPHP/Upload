<?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfoInterface;
use Upload\Upload;
use Upload\ValidationInterface;

/**
 * Class Size
 *
 * @package Upload\Validation
 */
class Size implements ValidationInterface
{
    /**
     * Minimum acceptable file size (bytes)
     *
     * @var int
     */
    protected $minSize;

    /**
     * Maximum acceptable file size (bytes)
     *
     * @var int
     */
    protected $maxSize;

    /**
     * @param int $maxSize Maximum acceptable file size in bytes (inclusive)
     * @param int $minSize Minimum acceptable file size in bytes (inclusive)
     */
    public function __construct($maxSize, $minSize = 0)
    {
        if (is_string($maxSize)) {
            $maxSize = Upload::humanReadableToBytes($maxSize);
        }
        $this->maxSize = $maxSize;

        if (is_string($minSize)) {
            $minSize = Upload::humanReadableToBytes($minSize);
        }
        $this->minSize = $minSize;
    }

    /**
     * @param FileInfoInterface $fileInfo
     * @throws Exception If validation fails
     */
    public function validate(FileInfoInterface $fileInfo)
    {
        $fileSize = $fileInfo->getSize();

        if ($fileSize < $this->minSize) {
            throw new Exception(sprintf('File size is too small. Must be greater than or equal to: %s', $this->minSize), $fileInfo);
        }

        if ($fileSize > $this->maxSize) {
            throw new Exception(sprintf('File size is too large. Must be less than: %s', $this->maxSize), $fileInfo);
        }
    }
}
