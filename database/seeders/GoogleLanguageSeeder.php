<?php

namespace Database\Seeders;

use App\Models\GoogleLanguage;
use Illuminate\Database\Seeder;

class GoogleLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fileData = fopen(dirname(__FILE__) . '/../data/google_languages.csv', 'r');
        fgetcsv($fileData); // skip header
        while(! feof($fileData)){
            $languageFields = fgetcsv($fileData);

            if($languageFields && count($languageFields) > 2) {
                GoogleLanguage::create(
                    [
                        'language_name' => $languageFields[0],
                        'language_code' => $languageFields[1],
                        'criterion_id' => $languageFields[2],
                    ]
                );
            }
        }
    }
}
