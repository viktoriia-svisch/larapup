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
        DB::table('students')->insert([
            'email' => 'student2@gmail.com',
            'password' => bcrypt('123456'),
            'first_name' => 'Iowa',
            'last_name' => 'Takahashi',
            'dateOfBirth' => '1999-04-14',
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
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('faculties')->insert([
            'name' => 'IT Advance',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('faculties')->insert([
            'name' => 'Topup Program',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('faculties')->insert([
            'name' => 'Chemistry Advance',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Spring Sprint',
            'description' => 'N/A',
            'start_date' => '2017-01-01',
            'end_date' => '2017-04-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Summer Sprint',
            'description' => 'N/A',
            'start_date' => '2017-04-01',
            'end_date' => '2017-07-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Fall Sprint',
            'description' => 'N/A',
            'start_date' => '2017-04-01',
            'end_date' => '2017-07-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Winter Sprint',
            'description' => 'N/A',
            'start_date' => '2017-07-01',
            'end_date' => '2017-10-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Spring Sprint',
            'description' => 'N/A',
            'start_date' => '2018-01-01',
            'end_date' => '2018-04-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Summer Sprint',
            'description' => 'N/A',
            'start_date' => '2018-04-01',
            'end_date' => '2018-07-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Fall Sprint',
            'description' => 'N/A',
            'start_date' => '2018-04-01',
            'end_date' => '2018-07-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Winter Sprint',
            'description' => 'N/A',
            'start_date' => '2018-07-01',
            'end_date' => '2018-10-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Spring Sprint',
            'description' => 'N/A',
            'start_date' => '2019-01-01',
            'end_date' => '2019-04-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Summer Sprint',
            'description' => 'N/A',
            'start_date' => '2019-04-01',
            'end_date' => '2019-07-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Fall Sprint',
            'description' => 'N/A',
            'start_date' => '2019-04-01',
            'end_date' => '2019-07-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
        DB::table('semesters')->insert([
            'name' => 'Winter Sprint',
            'description' => 'N/A',
            'start_date' => '2019-07-01',
            'end_date' => '2019-10-01',
            'created_at' => \Illuminate\Support\Carbon::now()
        ]);
    }
}
