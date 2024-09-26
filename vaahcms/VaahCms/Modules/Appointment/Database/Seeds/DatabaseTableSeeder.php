<?php
namespace VaahCms\Modules\Appointment\Database\Seeds;


use Illuminate\Database\Seeder;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class DatabaseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seeds();
        VaahSeeder::permissions(__DIR__.'/json/permissions.json');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    function seeds()
    {

    }


}
