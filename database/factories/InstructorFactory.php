<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_instructor' => $this->faker->unique()->numerify('INS####'),
            'codigo_barras' => $this->faker->unique()->numerify('BAR#########'),
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'documento_identidad' => $this->faker->unique()->numerify('##########'),
            'tipo_documento' => $this->faker->randomElement(['CC', 'CE', 'PA', 'TI']),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'direccion' => $this->faker->address(),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-2 years'),
            'hora_entrada_programada' => '07:00',
            'hora_salida_programada' => '16:00',
            'estado' => 'activo',
            'observaciones' => $this->faker->optional()->sentence(),
            'area_asignada' => $this->faker->randomElement(['Matemáticas', 'Lenguaje', 'Ciencias', 'Educación Física', 'Artes']),
            'cargo' => $this->faker->randomElement(['Docente', 'Coordinador', 'Especialista']),
        ];
    }
}
