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

        factory(App\User::class, 100)->create()->each(function ($u) {
            factory('App\Profile')->create(['user_id' => $u->id]);
        });

        for( $i = 0; $i < 200; $i++ ) {

            $users = User::inRandomOrder()->take(2)->get();
            $relationship = \App\Http\Controllers\RelationshipController
            ::friended($users[0]->id, $users[1]->id);

            if($relationship == null)
                App\Relationship::create([
                    'requestee' => $users[0]->id,
                    'requester' => $users[1]->id
                ]);
            //$relationship->blocked = ($relationship->status) ? false :
                //(bool) random_int(0, 1);

        }

    }

}
