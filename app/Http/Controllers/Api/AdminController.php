<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|min:3|max:10',
        ]);

        $worker = Worker::where('pin', $request->pin)->first();
        if (!$worker) {
            return response()->json([
                'ok' => false,
                'error' => 'PIN incorrecte.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'worker' => [
                'id' => $worker->id,
                'name' => $worker->name,
            ],
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $ivaPercentatge = 21;

        $totalAvui = (float) Order::whereDate('created_at', Carbon::today())
            ->where('status', 'Pagat')
            ->sum('total_price');
        $comandesComptador = (int) Order::whereDate('created_at', Carbon::today())
            ->where('status', 'Pagat')
            ->count();
        $efectiuAvui = (float) Order::whereDate('created_at', Carbon::today())
            ->where('payment_method', 'Efectiu')
            ->sum('total_price');
        $targetaAvui = (float) Order::whereDate('created_at', Carbon::today())
            ->where('payment_method', 'Targeta')
            ->sum('total_price');

        $baseImposable = $totalAvui / (1 + ($ivaPercentatge / 100));
        $quotaIva = $totalAvui - $baseImposable;
        $tiquetMig = $comandesComptador > 0 ? round($totalAvui / $comandesComptador, 2) : 0.0;

        $totalMes = (float) Order::where('status', 'Pagat')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->sum('total_price');

        $millorWorker = Worker::withCount([
            'orders' => function ($q) {
                $q->whereDate('created_at', Carbon::today())->where('status', 'Pagat');
            },
        ])->orderBy('orders_count', 'desc')->first();

        $topProductes = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_venuts'))
            ->with('product:id,name')
            ->whereHas('order', function ($q) {
                $q->where('status', 'Pagat');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_venuts')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => (int) $item->product_id,
                    'name' => $item->product->name ?? '(Producte eliminat)',
                    'total_venuts' => (float) $item->total_venuts,
                ];
            });

        $ingressosSetmana = [];
        $diesCurtCatala = ['Diu', 'Dil', 'Dim', 'Dmc', 'Dij', 'Div', 'Dis'];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i);
            $total = (float) Order::whereDate('created_at', $dia)
                ->where('status', 'Pagat')
                ->sum('total_price');
            $ingressosSetmana[] = [
                'label' => $diesCurtCatala[$dia->dayOfWeek] . ' ' . $dia->format('d/m'),
                'total' => round($total, 2),
                'date' => $dia->toDateString(),
            ];
        }

        $hoursRange = range(8, 15);
        $vendesRaw = Order::select(DB::raw('HOUR(created_at) as hora'), DB::raw('COUNT(*) as total'))
            ->where('status', 'Pagat')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->mapWithKeys(fn($item) => [(int) $item->hora => (int) $item->total]);

        $horesPunta = [];
        foreach ($hoursRange as $h) {
            $horesPunta[] = [
                'hour' => $h,
                'count' => $vendesRaw->get($h, 0),
            ];
        }

        $efectiuMes = (float) Order::where('status', 'Pagat')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->where('payment_method', 'Efectiu')
            ->sum('total_price');
        $targetaMes = (float) Order::where('status', 'Pagat')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->where('payment_method', 'Targeta')
            ->sum('total_price');

        $diesCatala = [
            0 => 'Diumenge', 1 => 'Dilluns', 2 => 'Dimarts', 3 => 'Dimecres',
            4 => 'Dijous', 5 => 'Divendres', 6 => 'Dissabte',
        ];
        $topPerDia = [];
        for ($dow = 0; $dow <= 6; $dow++) {
            $mysqlDow = $dow + 1;
            $items = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_venuts'))
                ->with('product:id,name')
                ->whereHas('order', function ($q) use ($mysqlDow) {
                    $q->where('status', 'Pagat')
                        ->whereRaw('DAYOFWEEK(created_at) = ?', [$mysqlDow]);
                })
                ->groupBy('product_id')
                ->orderByDesc('total_venuts')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'product_id' => (int) $item->product_id,
                        'name' => $item->product->name ?? '(Producte eliminat)',
                        'total_venuts' => (float) $item->total_venuts,
                    ];
                });
            $topPerDia[] = [
                'dow' => $dow,
                'name' => $diesCatala[$dow],
                'short' => $diesCurtCatala[$dow],
                'items' => $items,
            ];
        }

        return response()->json([
            'kpi' => [
                'total_today' => round($totalAvui, 2),
                'orders_today' => $comandesComptador,
                'cash_today' => round($efectiuAvui, 2),
                'card_today' => round($targetaAvui, 2),
                'ticket_avg' => $tiquetMig,
                'total_last_30d' => round($totalMes, 2),
                'best_worker' => $millorWorker ? $millorWorker->name : null,
            ],
            'caixa' => [
                'iva_percent' => $ivaPercentatge,
                'base_imposable' => round($baseImposable, 2),
                'iva_quota' => round($quotaIva, 2),
                'total_brut' => round($totalAvui, 2),
            ],
            'top_products' => $topProductes,
            'revenue_week' => $ingressosSetmana,
            'peak_hours' => $horesPunta,
            'payment_month' => [
                'cash' => round($efectiuMes, 2),
                'card' => round($targetaMes, 2),
            ],
            'top_per_day' => $topPerDia,
            'current_dow' => (int) Carbon::now()->format('w'),
        ]);
    }
}
