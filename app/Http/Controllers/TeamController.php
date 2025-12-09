<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    /**
     * Mostrar página de equipo/créditos
     */
    public function show(): Response
    {
        $teamMembers = TeamMember::getActivos();

        return Inertia::render('Team/Show', [
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * API: Obtener todos los miembros del equipo
     */
    public function getAll()
    {
        return response()->json([
            'success' => true,
            'data' => TeamMember::getActivos(),
        ]);
    }

    /**
     * API: Obtener miembros por rol
     */
    public function getByRole(string $rol)
    {
        return response()->json([
            'success' => true,
            'data' => TeamMember::getByRol($rol),
        ]);
    }
}
