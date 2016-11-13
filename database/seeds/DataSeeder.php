<?php

use App\Comment;
use App\Post;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(App\User::class, 10)->create()->each(function($user) {
            $posts = factory(Post::class, 5)->make();
            $user->posts()->saveMany($posts);
        });

        Post::all()->each(function($post) use ($users) {
            $users->random(3)->each(function($user) use ($post) {
                factory(Comment::class)->create(['post_id' => $post->id, 'user_id' => $user->id]);
            });
        });



    }
}
