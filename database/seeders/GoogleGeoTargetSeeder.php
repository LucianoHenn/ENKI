<?php

namespace Database\Seeders;

use App\Models\GoogleGeoTarget;
use Illuminate\Database\Seeder;

class GoogleGeoTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = dirname(__FILE__) . '/../data/geotargets.csv';
        $fileData = fopen($filePath, 'r');
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $this->command->getOutput()->progressStart($file->key() - 1);

        fgetcsv($fileData); // skip header
        while(! feof($fileData)){
            $languageFields = fgetcsv($fileData);

            if($languageFields && count($languageFields) > 2) {
                GoogleGeoTarget::create(
                    [
                       'criteria_id' => $languageFields[0],
                       'name' => $languageFields[1],
                       'canonical_name' => $languageFields[2],
                       'parent_id' => $languageFields[3],
                       'country_code' => $languageFields[4],
                       'target_type' => $languageFields[5],
                       'status' => $languageFields[6],
                    ]
                );
                $this->command->getOutput()->progressAdvance();
                usleep(10 * 1000);
            }
        }
    }
}
