<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class account extends Seeder
{
    public function run()
    {
        DB::table('admins')->insert([
            'email' => 'admin@admin.gw',
            'password' => bcrypt('123456'),
            'first_name' => 'John',
            'last_name' => 'Legend',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('students')->insert([
            'email' => 'student@gmail.com',
            'password' => bcrypt('123456'),
            'first_name' => 'Adam',
            'last_name' => 'Bentley',
            'dateOfBirth' => '1999-03-15',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('students')->insert([
            'email' => 'student1@gmail.com',
            'password' => bcrypt('123456'),
            'first_name' => 'Biserka',
            'last_name' => 'Nikola',
            'dateOfBirth' => '1999-04-15',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('coordinators')->insert([
            'email' => 'coor@gmail.com',
            'password' => bcrypt('123456'),
            'first_name' => 'Bentley',
            'last_name' => 'Biserka',
            'type' => 0,
            'dateOfBirth' => '1999-03-15',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('coordinators')->insert([
            'email' => 'coormaster@gmail.com',
            'password' => bcrypt('123456'),
            'first_name' => 'James',
            'last_name' => 'Edward',
            'type' => 1,
            'dateOfBirth' => '1999-03-15',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('faculties')->insert([
            'name' => 'Math Advance',
            'first_deadline'
        ]);
    }
}
