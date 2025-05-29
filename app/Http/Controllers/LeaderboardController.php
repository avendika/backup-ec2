<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    /**
     * Get general leaderboard (top players by score)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 50); // Default 50 players
            $offset = $request->input('offset', 0); // For pagination
            
            $leaderboard = User::select([
                'id',
                'username',
                'avatar',
                'score',
                'level',
                'created_at'
            ])
            ->orderBy('score', 'desc')
            ->orderBy('level', 'desc')
            ->orderBy('created_at', 'asc') // Earlier registration as tiebreaker
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($user, $index) use ($offset) {
                return [
                    'rank' => $offset + $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'score' => $user->score,
                    'level' => $user->level,
                    'joined_date' => $user->created_at->format('Y-m-d')
                ];
            });

            // Get total count for pagination
            $total = User::count();

            return response()->json([
                'success' => true,
                'data' => [
                    'leaderboard' => $leaderboard,
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'has_more' => ($offset + $limit) < $total
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's position in leaderboard
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserPosition(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get user's rank
            $rank = User::where('score', '>', $user->score)
                ->orWhere(function($query) use ($user) {
                    $query->where('score', $user->score)
                          ->where('level', '>', $user->level);
                })
                ->orWhere(function($query) use ($user) {
                    $query->where('score', $user->score)
                          ->where('level', $user->level)
                          ->where('created_at', '<', $user->created_at);
                })
                ->count() + 1;

            // Get total users
            $totalUsers = User::count();

            // Get users around current user (±5 positions)
            $startRank = max(1, $rank - 5);
            $endRank = min($totalUsers, $rank + 5);
            
            $nearbyUsers = User::select([
                'id',
                'username', 
                'avatar',
                'score',
                'level',
                'created_at'
            ])
            ->orderBy('score', 'desc')
            ->orderBy('level', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($endRank - $startRank + 1)
            ->offset($startRank - 1)
            ->get()
            ->map(function ($item, $index) use ($startRank, $user) {
                $currentRank = $startRank + $index;
                return [
                    'rank' => $currentRank,
                    'id' => $item->id,
                    'username' => $item->username,
                    'avatar' => $item->avatar,
                    'score' => $item->score,
                    'level' => $item->level,
                    'is_current_user' => $item->id === $user->id,
                    'joined_date' => $item->created_at->format('Y-m-d')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'current_user' => [
                        'rank' => $rank,
                        'username' => $user->username,
                        'avatar' => $user->avatar,
                        'score' => $user->score,
                        'level' => $user->level
                    ],
                    'nearby_users' => $nearbyUsers,
                    'total_users' => $totalUsers
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user position',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard by specific level
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getByLevel(Request $request): JsonResponse
    {
        try {
            $level = $request->input('level', 1);
            $limit = $request->input('limit', 20);
            
            $leaderboard = User::select([
                'id',
                'username',
                'avatar', 
                'score',
                'level',
                'created_at'
            ])
            ->where('level', $level)
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'score' => $user->score,
                    'level' => $user->level,
                    'joined_date' => $user->created_at->format('Y-m-d')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'level' => $level,
                    'leaderboard' => $leaderboard,
                    'total_players_at_level' => User::where('level', $level)->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard by level',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly leaderboard 
     * Based on users who joined or had high activity this week
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getWeekly(Request $request): JsonResponse
    {
        try {
            $weekStart = Carbon::now()->startOfWeek();
            $limit = $request->input('limit', 20);
            
            // For weekly, we'll show top performers by score
            // You might want to add a weekly_score column for actual weekly tracking
            $leaderboard = User::select([
                'id',
                'username',
                'avatar',
                'score', 
                'level',
                'created_at',
                'updated_at'
            ])
            ->whereDate('updated_at', '>=', $weekStart)
            ->orderBy('score', 'desc')
            ->orderBy('level', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'score' => $user->score,
                    'level' => $user->level,
                    'last_active' => $user->updated_at->format('Y-m-d H:i')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => 'weekly',
                    'week_start' => $weekStart->format('Y-m-d'),
                    'leaderboard' => $leaderboard
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weekly leaderboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly leaderboard
     * Based on users who joined or had high activity this month
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getMonthly(Request $request): JsonResponse
    {
        try {
            $monthStart = Carbon::now()->startOfMonth();
            $limit = $request->input('limit', 30);
            
            // For monthly, we'll show top performers by score
            // You might want to add a monthly_score column for actual monthly tracking
            $leaderboard = User::select([
                'id',
                'username',
                'avatar',
                'score',
                'level', 
                'created_at',
                'updated_at'
            ])
            ->whereDate('updated_at', '>=', $monthStart)
            ->orderBy('score', 'desc')
            ->orderBy('level', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'score' => $user->score,
                    'level' => $user->level,
                    'last_active' => $user->updated_at->format('Y-m-d H:i')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => 'monthly',
                    'month_start' => $monthStart->format('Y-m-d'),
                    'leaderboard' => $leaderboard
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch monthly leaderboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard statistics
     * 
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_players' => User::count(),
                'highest_score' => User::max('score') ?? 0,
                'highest_level' => User::max('level') ?? 1,
                'average_score' => round(User::avg('score') ?? 0, 2),
                'average_level' => round(User::avg('level') ?? 1, 2),
                'new_players_this_week' => User::where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
                'new_players_this_month' => User::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
                'level_distribution' => User::select('level', DB::raw('count(*) as count'))
                    ->groupBy('level')
                    ->orderBy('level')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'level' => $item->level,
                            'player_count' => $item->count
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
