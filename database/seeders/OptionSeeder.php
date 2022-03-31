<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         *  -------------------
         *  | Seeding Options |
         *  -------------------
         */
        DB::table('options')->insertOrIgnore([
            // User account status
            ['category' => 'status', 'code' => '1', 'display' => 'Active', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'status', 'code' => '0', 'display' => 'Disabled', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Days
            ['category' => 'day', 'code' => '1', 'display' => 'Monday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'day', 'code' => '2', 'display' => 'Tuesday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'day', 'code' => '3', 'display' => 'Wednesday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'day', 'code' => '4', 'display' => 'Thursday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'day', 'code' => '5', 'display' => 'Friday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'day', 'code' => '6', 'display' => 'Saturday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'day', 'code' => '7', 'display' => 'Sunday', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Months
            ['category' => 'month', 'code' => '1', 'display' => 'January', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '2', 'display' => 'February', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '3', 'display' => 'March', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '4', 'display' => 'April', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '5', 'display' => 'May', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '6', 'display' => 'June', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '7', 'display' => 'July', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '8', 'display' => 'August', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '9', 'display' => 'September', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '10', 'display' => 'October', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '11', 'display' => 'November', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'month', 'code' => '12', 'display' => 'December', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Malaysian States
            ['category' => 'states', 'code' => '1', 'display' => 'Johor Darul Ta\'zim', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '2', 'display' => 'Kedah Darul Aman', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '3', 'display' => 'Kelantan Darul Naim', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '4', 'display' => 'Melaka', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '5', 'display' => 'Negeri Sembilan Darul Khusus', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '6', 'display' => 'Pahang Darul Makmur', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '7', 'display' => 'Pulau Pinang', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '8', 'display' => 'Perak Darul Ridzuan', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '9', 'display' => 'Perlis Indera Kayangan', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '10', 'display' => 'Selangor Darul Ehsan', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '11', 'display' => 'Terengganu Darul Iman', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '12', 'display' => 'Sabah', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '13', 'display' => 'Sarawak', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '14', 'display' => 'Wilayah Persekutuan Kuala Lumpur', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '15', 'display' => 'Wilayah Persekutuan Labuan', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['category' => 'states', 'code' => '16', 'display' => 'Wilayah Persekutuan Putrajaya', 'description' => null, 'flag' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
