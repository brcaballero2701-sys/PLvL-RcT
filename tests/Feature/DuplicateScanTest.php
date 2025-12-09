<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Instructor;
use App\Models\Asistencia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DuplicateScanTest extends TestCase
{
    use RefreshDatabase;

    protected $guardia;
    protected $instructor;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear guardia autenticado
        $this->guardia = User::factory()->create(['role' => 'guardia']);

        // Crear instructor
        $this->instructor = Instructor::factory()->create([
            'documento_identidad' => '12345678',
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'estado' => 'activo'
        ]);
    }

    /**
     * RF032: Validar que rechaza doble escaneo del mismo tipo en 5 minutos
     */
    public function test_rejects_duplicate_entry_scan_within_5_minutes(): void
    {
        // Primer escaneo: entrada
        $response1 = $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '12345678',
            'tipo_registro' => 'entrada'
        ]);

        $response1->assertStatus(200)
                  ->assertJsonPath('success', true);

        // Segundo escaneo: entrada (duplicado dentro de 5 min) - DEBE RECHAZAR
        $response2 = $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '12345678',
            'tipo_registro' => 'entrada'
        ]);

        $response2->assertStatus(409) // 409 Conflict
                  ->assertJsonPath('success', false)
                  ->assertJsonPath('codigo_error', 'DUPLICATE_SCAN')
                  ->assertJsonPath('message', 'Doble escaneo detectado. Espere antes de intentar de nuevo.');
    }

    /**
     * RF032: Permitir cambio de entrada a salida (no es duplicado)
     */
    public function test_allows_exit_scan_after_entry_scan(): void
    {
        // Primer escaneo: entrada
        $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '12345678',
            'tipo_registro' => 'entrada'
        ])->assertStatus(200);

        // Segundo escaneo: salida (diferente tipo) - DEBE PERMITIR
        $response = $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '12345678',
            'tipo_registro' => 'salida'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    /**
     * RF032: Permitir nuevo escaneo después de 5 minutos
     */
    public function test_allows_duplicate_scan_after_5_minutes(): void
    {
        // Primer escaneo
        $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '12345678',
            'tipo_registro' => 'entrada'
        ])->assertStatus(200);

        // Simular que pasaron 5+ minutos
        $oldAsistencia = Asistencia::first();
        $oldAsistencia->update(['fecha_hora_registro' => now()->subMinutes(6)]);

        // Segundo escaneo después de 5 min - DEBE PERMITIR
        $response = $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '12345678',
            'tipo_registro' => 'entrada'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    /**
     * RF032: Rechaza instructor no encontrado
     */
    public function test_rejects_unknown_instructor(): void
    {
        $response = $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'cedula' => '99999999',
            'tipo_registro' => 'entrada'
        ]);

        $response->assertStatus(404)
                 ->assertJsonPath('success', false)
                 ->assertJsonPath('message', 'Instructor no encontrado con esa cédula');
    }

    /**
     * RF032: Valida campos requeridos - Prueba con cedula ausente
     */
    public function test_validates_required_fields(): void
    {
        // Cuando no se envía cedula, debe fallar la validación
        $response = $this->actingAs($this->guardia)->postJson('/guardia/registrar-asistencia', [
            'tipo_registro' => 'entrada'
        ]);

        // Esperar status 422 (Unprocessable Entity) por validación fallida
        $response->assertStatus(422);
    }
}
