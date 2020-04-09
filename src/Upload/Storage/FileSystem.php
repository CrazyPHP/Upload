<?php

namespace Upload\Storage;

use Upload\Exception;
use Upload\FileInfoInterface;
use Upload\StorageInterface;

/**
 * Class FileSystem
 * @package Upload\Storage
 */
class FileSystem implements StorageInterface
{
    /**
     * Path to upload destination directory (with trailing slash)
     * @var string
     */
    protected $directory;

    /**
     * Overwrite existing files?
     * @var bool
     */
    protected $overwrite;

    /**
     * @param string $directory Relative or absolute path to upload directory
     * @param bool $overwrite Should this overwrite existing files?
     * @throws \InvalidArgumentException If directory does not exist
     * @throws \InvalidArgumentException If directory is not writable
     */
    public function __construct($directory, $overwrite = false)
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException('Directory does not exist');
        }
        if (!is_writable($directory)) {
            throw new \InvalidArgumentException('Directory is not writable');
        }
        $this->directory = rtrim($directory, '/') . DIRECTORY_SEPARATOR;
        $this->overwrite = (bool)$overwrite;
    }

    /**
     * @param FileInfoInterface $fileInfo The file object to upload
     * @throws Exception If overwrite is false and file already exists
     * @throws Exception If error moving file to destination
     */
    public function store(FileInfoInterface $fileInfo)
    {
        $destinationFile = $this->directory . $fileInfo->getNameWithExtension();
        if ($this->overwrite === false && file_exists($destinationFile) === true) {
            throw new Exception('File already exists', $fileInfo);
        }

        if ($fileInfo->isUploadedFile() && $this->moveUploadedFile($fileInfo->getPathname(), $destinationFile) === false) {
            throw new Exception('Uploaded file could not be moved to final destination.', $fileInfo);
        }

        if (!$fileInfo->isUploadedFile() && rename($fileInfo->getPathname(), $destinationFile) === false) {
            throw new Exception('File could not be moved to final destination.', $fileInfo);
        }
    }

    /**
     * @param string $source The source file
     * @param string $destination The destination file
     * @return bool
     */
    protected function moveUploadedFile($source, $destination)
    {
        return move_uploaded_file($source, $destination);
    }
}
