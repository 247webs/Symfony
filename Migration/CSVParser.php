<?php

namespace AppBundle\Migration;

class CSVParser
{
    /**
     * @param array $files
     * @return array
     */
    public function parse(array $files)
    {
        $rows = [];

        foreach ($files as $file) {
            $i = 0;
            while (($line = fgetcsv($file)) !== false) {
                $i++;
                if (1 === $i) { // skip header row
                    continue;
                }

                $rows[] = [
                    'userId' => $line[0],
                    'companyName' => $line[1],
                    'firstName' => $line[2],
                    'lastName' => $line[3],
                    'plan' => $line[4],
                    'reseller' => $line[5],
                    'payeeLevel' => $line[6]
                ];
            }
        }

        return $rows;
    }
}
