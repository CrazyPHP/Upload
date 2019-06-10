<?php

namespace Upload;

/**
 * Interface StorageInterface
 *
 * @package Upload
 */
interface StorageInterface
{
    /**
     * Store file
     *
     * @param FileInfoInterface $fileInfo
     * @throws Exception
     */
    public function store(FileInfoInterface $fileInfo);
}
