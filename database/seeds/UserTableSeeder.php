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

//        factory(App\User::class, 100)->create()->each(function ($u) {
//            factory('App\Profile')->create(['user_id' => $u->id]);
//        });

        for( $i = 0; $i<400; $i++ ) {
            $users = User::inRandomOrder()->take(2)->get();
            $userOneId = $users[0]->id;
            $userTwoId = $users[1]->id;

            $relationship = \App\Relationship::where([
                ['requester', '=', $userOneId],
                ['requestee', '=', $userOneId],
                ['status', '=', true]
            ])->orWhere([
                ['requester', '=', $userTwoId],
                ['requestee', '=', $userOneId],
                ['status', '=', true]
            ])->first();

            if(!$relationship)
                \App\Relationship::create([
                    'requester' => $userOneId,
                    'requestee' => $userTwoId,
                    'status' => true
                ]);
//            $relationship = factory('App\Relationship')->create(['requestee' => $users[0], 'requester' => $users[1], 'status' => (bool) random_int(0, 1)]);
//            $relationship->blocked = ($relationship->status) ? false : (bool) random_int(0, 1);
        }

    }

}
