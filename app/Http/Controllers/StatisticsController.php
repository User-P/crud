<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
  /**
   * Obtener estadísticas generales de usuarios
   */
  public function userStatistics(): JsonResponse
  {
    $this->authorize('viewAny', User::class); // Solo admins

    $stats = [
      // Conteos básicos
      'total_users' => User::count(),
      'users_by_role' => User::selectRaw('role, COUNT(*) as count')
        ->groupBy('role')
        ->pluck('count', 'role'),

      // Verificación de email
      'verified_users' => User::whereNotNull('email_verified_at')->count(),
      'unverified_users' => User::whereNull('email_verified_at')->count(),

      // Registros por mes (últimos 6 meses)
      'registrations_by_month' => $this->getRegistrationsByMonth(),

      // Usuarios recientes
      'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'role', 'created_at']),

      // Métricas adicionales
      'admin_percentage' => round((User::where('role', 'admin')->count() / User::count()) * 100, 2),
      'verification_rate' => round((User::whereNotNull('email_verified_at')->count() / User::count()) * 100, 2),
    ];

    return response()->json([
      'success' => true,
      'message' => 'Estadísticas de usuarios obtenidas correctamente',
      'data' => $stats,
      'generated_at' => now()->toISOString(),
    ]);
  }

  /**
   * Obtener estadísticas de actividad (tokens activos)
   */
  public function activityStatistics(): JsonResponse
  {
    $this->authorize('viewAny', User::class); // Solo admins

    $stats = [
      'active_tokens' => DB::table('personal_access_tokens')
        ->whereNull('expires_at')
        ->orWhere('expires_at', '>', now())
        ->count(),

      'tokens_by_user' => DB::table('personal_access_tokens')
        ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
        ->where('personal_access_tokens.tokenable_type', 'App\\Models\\User')
        ->selectRaw('users.name, COUNT(*) as token_count')
        ->groupBy('users.id', 'users.name')
        ->orderBy('token_count', 'desc')
        ->limit(10)
        ->get(),

      'recent_token_activity' => DB::table('personal_access_tokens')
        ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
        ->where('personal_access_tokens.tokenable_type', 'App\\Models\\User')
        ->selectRaw('users.name, personal_access_tokens.name as token_name, personal_access_tokens.last_used_at')
        ->whereNotNull('personal_access_tokens.last_used_at')
        ->orderBy('personal_access_tokens.last_used_at', 'desc')
        ->limit(10)
        ->get(),
    ];

    return response()->json([
      'success' => true,
      'message' => 'Estadísticas de actividad obtenidas correctamente',
      'data' => $stats,
      'generated_at' => now()->toISOString(),
    ]);
  }

  /**
   * Dashboard con resumen general
   */
  public function dashboard(): JsonResponse
  {
    $this->authorize('viewAny', User::class); // Solo admins

    $dashboard = [
      // Métricas principales
      'main_metrics' => [
        'total_users' => User::count(),
        'total_admins' => User::where('role', 'admin')->count(),
        'verified_users' => User::whereNotNull('email_verified_at')->count(),
        'active_tokens' => DB::table('personal_access_tokens')
          ->whereNull('expires_at')
          ->orWhere('expires_at', '>', now())
          ->count(),
      ],

      // Tendencias (comparación con mes anterior)
      'trends' => [
        'new_users_this_month' => User::whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count(),
        'new_users_last_month' => User::whereMonth('created_at', now()->subMonth()->month)
          ->whereYear('created_at', now()->subMonth()->year)
          ->count(),
      ],

      // Actividad reciente
      'recent_activity' => [
        'latest_registrations' => User::latest()->take(5)
          ->get(['name', 'email', 'role', 'created_at']),
        'recent_logins' => DB::table('personal_access_tokens')
          ->join('users', 'users.id', '=', 'personal_access_tokens.tokenable_id')
          ->selectRaw('users.name, personal_access_tokens.created_at as login_time')
          ->orderBy('personal_access_tokens.created_at', 'desc')
          ->limit(5)
          ->get(),
      ],
    ];

    // Calcular growth rate
    $thisMonth = $dashboard['trends']['new_users_this_month'];
    $lastMonth = $dashboard['trends']['new_users_last_month'];
    $dashboard['trends']['growth_rate'] = $lastMonth > 0
      ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2)
      : ($thisMonth > 0 ? 100 : 0);

    return response()->json([
      'success' => true,
      'message' => 'Dashboard obtenido correctamente',
      'data' => $dashboard,
      'generated_at' => now()->toISOString(),
    ]);
  }

  /**
   * Obtener registros por mes (últimos 6 meses)
   */
  private function getRegistrationsByMonth(): array
  {
    $months = [];

    for ($i = 5; $i >= 0; $i--) {
      $date = Carbon::now()->subMonths($i);
      $count = User::whereMonth('created_at', $date->month)
        ->whereYear('created_at', $date->year)
        ->count();

      $months[] = [
        'month' => $date->format('Y-m'),
        'month_name' => $date->translatedFormat('F Y'),
        'count' => $count,
      ];
    }

    return $months;
  }
}
