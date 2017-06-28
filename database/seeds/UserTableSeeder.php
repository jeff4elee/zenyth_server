<?php

use Illuminate\Database\Seeder;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 80)->create();

        for( $i = 0; $i<20; $i++ ) {

            $users = User::inRandomOrder()->take(2)->get();
            $relationship = factory('App\Relationship')->create(['requestee' => $users[0], 'requester' => $users[1], 'status' => (bool) random_int(0, 1)]);
            $relationship->blocked = ($relationship->status) ? false : (bool) random_int(0, 1);

        }

    }

}
