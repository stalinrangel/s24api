<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
        	[
        		'name' => 'Freddy Ramirez',
        		'email' => 'ramirez.fred@hotmail.com',
        		'password' => Hash::make('123456789')
        	]
        ];

	    foreach ($users as $user) {
	    	\App\User::create($user);
	    }
    }
}
