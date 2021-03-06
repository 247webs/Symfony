<?php

namespace AppBundle\Model;

use AppBundle\Validator\Constraints as CustomConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserImportList
 * @package AppBundle\Model
 */
class UserImportList
{
    /**
     * @Assert\NotBlank(
     *     message="Map is required"
     * )
     * @CustomConstraint\UserImportMapConstraint(
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
     *     message="Branch is required"
     * )
     */
    private $branch;

    /**
     * @Assert\NotNull(
     *     message="Please indicate whether the CSV file includes a header row"
     * )
     */
    private $fileIncludesHeaderRow;


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
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param mixed $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
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
}
