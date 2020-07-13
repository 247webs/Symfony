<?php

namespace AppBundle\Model;

use AppBundle\Validator\Constraints as CustomConstraint;
use Symfony\Component\Validator\Constraints as Assert;

class OfferImportList
{
    /**
     * @Assert\NotBlank(
     *     message="Map is required"
     * )
     * @CustomConstraint\OfferImportMapConstraint(
     *     message="Map is invalid"
     * )
     */
    private $map;

    /**
     * @Assert\NotBlank(
     *     message="File is required"
     * )
     * @Assert\File(
     *     mimeTypes={
     *          "text/csv",
     *          "text/plain",
     *          "application/csv",
     *          "application/excel",
     *          "application/vnd.ms-excel",
     *          "application/vnd.msexcel",
     *          "text/anytext",
     *          "text/comma-separated-values"
     *      },
     *     mimeTypesMessage="Invalid mime type"
     * )
     */
    private $file;

    /**
     * @Assert\NotNull(
     *     message="Please indicate whether the CSV file includes a header row"
     * )
     */
    private $fileIncludesHeaderRow;

    /**
     * @Assert\NotNull(
     *     message="Username is required"
     * )
     */
    private $username;


    /**
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param mixed $map
     */
    public function setMap($map)
    {
        $this->map = $map;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFileIncludesHeaderRow()
    {
        return $this->fileIncludesHeaderRow;
    }

    /**
     * @param mixed $fileIncludesHeaderRow
     */
    public function setFileIncludesHeaderRow($fileIncludesHeaderRow)
    {
        $this->fileIncludesHeaderRow = $fileIncludesHeaderRow;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

}
