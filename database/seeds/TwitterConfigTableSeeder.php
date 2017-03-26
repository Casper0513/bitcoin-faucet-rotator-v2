<?php

use App\Models\TwitterConfig;
use App\Models\User;
use Illuminate\Database\Seeder;

class TwitterConfigTableSeeder extends Seeder
{
    public function run()
    {
        $keys = [];
        $user = User::find(1);
        $twitterConfig = new TwitterConfig();

        $keys['consumer_key'] = env('CONSUMER_KEY');
        $keys['consumer_key_secret'] = env('CONSUMER_KEY_SECRET');
        $keys['access_token'] = env('ACCESS_TOKEN');
        $keys['access_token_secret'] = env('ACCESS_TOKEN_SECRET');
        $keys['user_id'] = $user->id;

        $twitterConfig->fill($keys);
        $twitterConfig->save();
    }
}
