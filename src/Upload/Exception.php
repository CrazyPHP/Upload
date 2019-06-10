<?php

namespace Upload;

/**
 * Class Exception
 *
 * @package Upload
 */
class Exception extends \RuntimeException
{
    /**
     * @var FileInfoInterface
     */
    protected $fileInfo;

    /**
     * @param string $message
     * @param FileInfoInterface $fileInfo
     */
    public function __construct($message, FileInfoInterface $fileInfo = null)
    {
        $this->fileInfo = $fileInfo;

        parent::__construct($message);
    }

    /**
     * @return FileInfoInterface
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }
}
