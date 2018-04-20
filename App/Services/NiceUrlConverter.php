<?php

namespace App\Services;

class NiceUrlConverter
{
    /**
     * @param string $cleanString
     * @param string $delimiter
     *
     * @return null|string|string[]
     */
    public function getCleanString(string $cleanString, string $delimiter = '-')
    {
        $cleanString = iconv('UTF-8', 'ASCII//TRANSLIT', $cleanString);
        $cleanString = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $cleanString);
        $cleanString = strtolower(trim($cleanString, '-'));
        $cleanString = preg_replace("/[\/_|+ -]+/", $delimiter, $cleanString);

        return $cleanString;
    }
}
