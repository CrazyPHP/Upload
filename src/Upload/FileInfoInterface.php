<?php

namespace Upload;

/**
 * Interface FileInfoInterface
 *
 * @package Upload
 */
interface FileInfoInterface
{
    public function getPathname();

    public function getName();

    public function setName($name);

    public function getExtension();

    public function setExtension($extension);

    public function getNameWithExtension();

    public function getMimetype();

    public function getSize();

    public function getHash();

    public function getDimensions();

    public function isUploadedFile();
}
