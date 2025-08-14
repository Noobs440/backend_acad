<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nom_user'=>'Dongmo Russel',
            "email"=>'russeldongmo05@gmail.com',
            'password'=> bcrypt('12345678'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI0559',
            //'role'=>'admin'
        ]);

        User::create([
            'nom_user'=>'Hyacinthe Urbain',
            "email"=>'hyancintheurbainkamtemba@gmail.com',
            'password'=> bcrypt('12345678'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI2554',
            'role'=>'admin',
        ]);

        User::create([
            'nom_user'=>'jean',
            "email"=>'arieldoubissi4@gmail.com',
            'password'=> bcrypt('20056663'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI9991',
            'role'=>'adminsys',
        ]);

        User::create([
            'nom_user'=>'Adminsys 2',
            "email"=>'adminsys2@example.com',
            'password'=> bcrypt('adminsys123'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI9992',
            'role'=>'adminsys',
        ]);
    
        User::create([
            'nom_user'=>'Fosso Cabrel',
            "email"=>'fossocabrel08@gmail.com',
            'password'=> bcrypt('12345678'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI0553',
        ]);

        User::create([
            'nom_user'=>'Adriene Bei',
            "email"=>'adrienesonfack@gmail.com',
            'password'=> bcrypt('00000000'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI0589',
            'role'=>'superviseur'
        ]);

        User::create([
            'nom_user'=>'mike utrains',
            "email"=>'mike.utrains@gmail.com',
            'password'=> bcrypt('1234'),
            'tbl_filiere_id'=>'1',
            'matricule' =>'CM-UDS-22SCI0552',
        ]);

        User::create([
            'nom_user'=>'fozing lise',
            "email"=>'fozinglise@gmail.com',
            'password'=> bcrypt('4321'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI0558'
        ]);

        User::create([
            'nom_user'=>'mike diogni',
            "email"=>'mikediogni@gmail.com',
            'password'=> bcrypt('1234'),
            'tbl_filiere_id'=>'1',
            'matricule' => 'CM-UDS-22SCI0554',
            'role'=>'admin',
        ]);
    }
}
