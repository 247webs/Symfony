<?php

namespace AppBundle\Utilities;

class FilterUtilities
{
    /**
     * @param string $string
     * @return array
     */
    public static function explodeFilters($string)
    {
        $return = [];
        $filters = explode('|', $string);

        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $filter) {
                $keyValues = explode('~', $filter);

                if (is_array($keyValues) && array_key_exists(1, $keyValues)) {
                    $obj = new \stdClass();
                    $obj->filterBy = trim($keyValues[0]);
                    $obj->filterValue = trim($keyValues[1]);
                    $return[] = $obj;
                    unset($obj);
                }
            }
        }

        return $return;
    }
}
