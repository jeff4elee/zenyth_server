<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSearchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $first_name_list = array('Aob', 'Bob', 'Rob', 'Jeff', 'Hoang');
        $last_name_list = array('Aob', 'Bob', 'Cob', 'Dob', 'Eob', 'Fob', 'Rob');

        factory(App\User::class, 30)->create()->each(function($u) use ($first_name_list, $last_name_list){
            factory('App\Profile')->create(['user_id'=>$u->id, 'first_name' => $first_name_list[random_int(0, sizeof($first_name_list)-1)], 'last_name' => $last_name_list[random_int(0, sizeof($last_name_list)-1)]]);
        });

        for( $i = 0; $i<10; $i++ ) {

            $users = User::inRandomOrder()->take(2)->get();
            $relationship = factory('App\Relationship')->create(['requestee' => $users[0], 'requester' => $users[1], 'status' => (bool) random_int(0, 1)]);
            $relationship->blocked = ($relationship->status) ? false : (bool) random_int(0, 1);

        }

    }
}
