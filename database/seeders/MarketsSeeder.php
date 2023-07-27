<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Market;
class MarketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $markets = json_decode(file_get_contents(dirname(__FILE__) . '/../data/markets.json'));
        $this->command->getOutput()->progressStart(count($markets));


        foreach($markets as $market) {
            Market::create(
                [
                    'code' => strtolower($market->Code),
                    'name' => $market->Name,
                    'status' => true
                ]
            );
            $this->command->getOutput()->progressAdvance();
            usleep(10 * 1000);
        }

    }
}
