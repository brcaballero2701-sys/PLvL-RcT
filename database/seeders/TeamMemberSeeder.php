<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos existentes
        TeamMember::truncate();

        // Datos del equipo
        $teamMembers = [
            [
                'nombre' => 'Yesica Paola Carrascal Quintero',
                'rol' => 'Instructora líder',
                'celular' => '3112395817',
                'email' => null,
                'cedula' => null,
                'descripcion' => 'Instructora líder del proyecto',
                'foto_url' => null,
                'orden' => 1,
                'activo' => true,
            ],
            [
                'nombre' => 'Diego Armando Quintero Contreras',
                'rol' => 'Desarrollador 1',
                'celular' => null,
                'email' => null,
                'cedula' => null,
                'descripcion' => 'Desarrollador Full Stack',
                'foto_url' => null,
                'orden' => 2,
                'activo' => true,
            ],
            [
                'nombre' => 'Kevy Duvan Coronel Caballero',
                'rol' => 'Desarrollador 2',
                'celular' => '3004907439',
                'email' => 'caballerokevin418@gmail.com',
                'cedula' => null,
                'descripcion' => 'Desarrollador Full Stack',
                'foto_url' => null,
                'orden' => 3,
                'activo' => true,
            ],
            [
                'nombre' => 'George Jesus Vera Pallarez',
                'rol' => 'Desarrollador 3',
                'celular' => '1003257664',
                'email' => null,
                'cedula' => '1003257664',
                'descripcion' => 'Desarrollador Full Stack',
                'foto_url' => null,
                'orden' => 4,
                'activo' => true,
            ],
        ];

        // Insertar los datos
        foreach ($teamMembers as $member) {
            TeamMember::create($member);
        }
    }
}
