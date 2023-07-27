<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = json_decode(file_get_contents(dirname(__FILE__) . '/../data/countries.json'));
        $this->command->getOutput()->progressStart(count($countries));


        foreach($countries as $country) {
            Country::create(
                [
                    'code' => strtolower($country->code),
                    'name' => $country->name,
                ]
            );
            $this->command->getOutput()->progressAdvance();
            usleep(10 * 1000);
        }
    }
}
