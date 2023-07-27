<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Facebook\Site;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SitesImport implements ToCollection, WithHeadingRow
{
    /**
     * Import data from excel file.
     *
     * @param $collection
     * @return void
     */
    public function collection( $collection ): void
    {
        foreach($collection as $row) {
            if ($this->isValidFormat($row)) {
                continue;
            }

            $site = Site::updateOrCreate(
                [
                    'name' => $row['name'],
                ],
                [
                    'display_name' => $row['display_name'],
                    'template_url' => $row['template_url'],
                    'status' => $row['status'],
                ]
            );

            $countryIds = $this->getCountryIds($row['countries']);
            $site->countries()->sync($countryIds);
        }
    }

    /**
     * Check if the row is valid.
     *
     * @param array $row The row to check.
     * @return bool
     */
    public function isValidFormat($row): bool
    {
        return !isset($row['name']) || !isset($row['display_name']) || !isset($row['template_url']) || !isset($row['countries']) || !isset($row['status']);
    }

    /**
     * Get the country ids from the country code.
     *
     * @param string $countries The country codes.
     * @return array The country ids.
     */
    public function getCountryIds($countries): array
    {
        $countryIds = [];
        $countries = explode(',', $countries);
        $countries = array_map('trim', $countries);
        $countries = array_map('strtolower', $countries);

        foreach ($countries as $country) {
            $countryObject = Country::where('code', $country)->first();
            if ($countryObject) {
                $countryIds[] = $countryObject->id;
            }
        }

        return $countryIds;
    }
}
