<?php

namespace Upload;

/**
 * Interface FileInfoInterface
 *
 * @package Upload
 */
interface FileInfoInterface
{
    /**
     * @return string
     */
    public function getPathname();

    /**
     * @return string
     */
    public function getName();

    public function setName($name);

    /**
     * @return string
     */
    public function getExtension();

    public function setExtension($extension);

    /**
     * @return string
     */
    public function getNameWithExtension();

    /**
     * @return string
     */
    public function getMimetype();

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param string $algorithm sha256|md5
     * @return string
     */
    public function getHash($algorithm = 'sha256');

    /**
     * @return array
     */
    public function getDimensions();

    /**
     * @return bool
     */
    public function isUploadedFile();
}
