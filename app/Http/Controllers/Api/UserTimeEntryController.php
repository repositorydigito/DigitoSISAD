<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class UserTimeEntryController extends Controller
{
    /**
     * Obtener usuarios con sus horas registradas
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filtrar por fechas si se proporcionan
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // Añadir información de horas en el rango de fechas
            $query->withCount(['timeEntries as total_hours_in_range' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                      ->select(DB::raw('SUM(hours)'));
            }]);
            
            // Filtrar por proyecto si se proporciona
            if ($request->has('project_id')) {
                $projectId = $request->input('project_id');
                
                // Añadir información de horas en el proyecto y rango de fechas
                $query->withCount(['timeEntries as total_hours_in_project_range' => function ($query) use ($startDate, $endDate, $projectId) {
                    $query->where('project_id', $projectId)
                          ->whereBetween('date', [$startDate, $endDate])
                          ->select(DB::raw('SUM(hours)'));
                }]);
                
                // Solo incluir usuarios que tienen horas en este proyecto
                $query->whereHas('timeEntries', function ($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                });
            }
        } else {
            // Añadir información de horas totales
            $query->withCount(['timeEntries as total_hours' => function ($query) {
                $query->select(DB::raw('SUM(hours)'));
            }]);
            
            // Filtrar por proyecto si se proporciona
            if ($request->has('project_id')) {
                $projectId = $request->input('project_id');
                
                // Añadir información de horas en el proyecto
                $query->withCount(['timeEntries as total_hours_in_project' => function ($query) use ($projectId) {
                    $query->where('project_id', $projectId)
                          ->select(DB::raw('SUM(hours)'));
                }]);
                
                // Solo incluir usuarios que tienen horas en este proyecto
                $query->whereHas('timeEntries', function ($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                });
            }
        }
        
        // Cargar proyectos relacionados
        $query->with('projects');
        
        // Ordenar por nombre
        $query->orderBy('name');
        
        return UserResource::collection($query->get());
    }
    
    /**
     * Obtener estadísticas de horas por usuario
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        // Filtrar por fechas si se proporcionan
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            $statistics = DB::table('time_entries')
                ->join('users', 'time_entries.user_id', '=', 'users.id')
                ->join('projects', 'time_entries.project_id', '=', 'projects.id')
                ->whereBetween('time_entries.date', [$startDate, $endDate])
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('SUM(time_entries.hours) as total_hours'),
                    DB::raw('COUNT(DISTINCT time_entries.project_id) as projects_count')
                )
                ->groupBy('users.id', 'users.name')
                ->orderBy('total_hours', 'desc')
                ->get();
        } else {
            $statistics = DB::table('time_entries')
                ->join('users', 'time_entries.user_id', '=', 'users.id')
                ->join('projects', 'time_entries.project_id', '=', 'projects.id')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('SUM(time_entries.hours) as total_hours'),
                    DB::raw('COUNT(DISTINCT time_entries.project_id) as projects_count')
                )
                ->groupBy('users.id', 'users.name')
                ->orderBy('total_hours', 'desc')
                ->get();
        }
        
        return response()->json([
            'data' => $statistics
        ]);
    }
}
