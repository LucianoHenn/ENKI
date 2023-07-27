<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = json_decode(file_get_contents(dirname(__FILE__) . '/../data/languages.json'));
        $this->command->getOutput()->progressStart(count($languages));


        foreach($languages as $language) {
            Language::create(
                [
                    'code' => strtolower($language->code),
                    'name' => $language->name,
                ]
            );
            $this->command->getOutput()->progressAdvance();
            usleep(10 * 1000);
        }
    }
}
