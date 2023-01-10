<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Country;
use App\Models\device;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $user=new User();
        $user->name='Admin';
        $user->email='admin@gmail.com';
        $user->password=bcrypt('me123');
        $user->status='Active';
        $user->save();
        $device=new device();
        $device->name='Vivo 221';
        $device->device_id='1';
        $device->save();

    }
}
