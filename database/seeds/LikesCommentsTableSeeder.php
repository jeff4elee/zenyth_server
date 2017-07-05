<?php

use Illuminate\Database\Seeder;

class LikesCommentsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        factory('App\Comment', 5)->create()->each(function($c){
            factory('App\Like', random_int(0, 2))->create(['entity_id' => $c->entity_id]);
            factory('App\Like', random_int(0, 2))->create(['entity_id' => $c->on_entity_id]);
        });

        factory('App\Like', 5)->create();

    }

}