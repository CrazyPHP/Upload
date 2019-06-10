<?php

namespace Upload;

/**
 * Interface ValidationInterface
 *
 * @package Upload
 */
interface ValidationInterface
{
    /**
     * Validate file
     *
     * @param FileInfoInterface $fileInfo
     * @throws Exception
     */
    public function validate(\Upload\FileInfoInterface $fileInfo);
}
