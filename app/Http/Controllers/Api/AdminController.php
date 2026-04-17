<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

    // --- Categories CRUD ---
    public function listCategories(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return response()->json([
            'categories' => $categories->map(fn(Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'color' => $c->color,
                'products_count' => (int) ($c->products_count ?? 0),
            ])->values(),
        ]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'color' => 'nullable|string|max:20',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return response()->json([
            'ok' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'products_count' => 0,
            ],
        ], 201);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'color' => 'nullable|string|max:20',
        ]);

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return response()->json([
            'ok' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'products_count' => (int) $category->products()->count(),
            ],
        ]);
    }

    public function destroyCategory(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['ok' => true]);
    }

    // --- Products CRUD ---
    public function listProducts(): JsonResponse
    {
        $products = Product::with('categories:id,name,color')
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => $products->map(fn(Product $p) => $this->mapProduct($p))->values(),
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $data = $this->validateProduct($request);

        $product = Product::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
            'is_gluten_free' => (bool) ($data['is_gluten_free'] ?? false),
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : true,
        ]);
        $product->categories()->sync([$data['category_id']]);
        $product->load('categories:id,name,color');

        return response()->json([
            'ok' => true,
            'product' => $this->mapProduct($product),
        ], 201);
    }

    public function updateProduct(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $this->validateProduct($request, $product->id);

        $product->update([
            'name' => $data['name'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? $product->stock,
            'is_gluten_free' => array_key_exists('is_gluten_free', $data)
                ? (bool) $data['is_gluten_free']
                : $product->is_gluten_free,
            'description' => $data['description'] ?? $product->description,
            'image_path' => array_key_exists('image_path', $data)
                ? $data['image_path']
                : $product->image_path,
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : $product->active,
        ]);
        $product->categories()->sync([$data['category_id']]);
        $product->load('categories:id,name,color');

        return response()->json([
            'ok' => true,
            'product' => $this->mapProduct($product),
        ]);
    }

    public function destroyProduct(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['ok' => true]);
    }

    public function uploadProductImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = 'prod_' . now()->format('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

        $targetDir = public_path('images/productes');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        $file->move($targetDir, $filename);

        $relative = 'images/productes/' . $filename;
        return response()->json([
            'ok' => true,
            'path' => $relative,
            'url' => asset($relative),
        ]);
    }

    protected function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $nameRule = 'required|string|max:255';
        return $request->validate([
            'name' => $nameRule,
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_gluten_free' => 'nullable|boolean',
            'description' => 'nullable|string|max:2000',
            'image_path' => 'nullable|string|max:2048',
            'active' => 'nullable|boolean',
        ]);
    }

    // --- Orders history ---
    public function listOrders(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100',
            'status' => 'nullable|string|in:Pagat,Pendent,Anullat,Encarrec',
            'payment_method' => 'nullable|string|in:Efectiu,Targeta,Mixta',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'worker_id' => 'nullable|integer|exists:workers,id',
            'search' => 'nullable|string|max:120',
        ]);

        $query = Order::with(['worker:id,name', 'items:id,order_id,product_id,quantity,price_at_sale'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }
        if ($request->filled('search')) {
            $needle = trim((string) $request->search);
            $query->where(function ($q) use ($needle) {
                $q->where('customer_name', 'like', "%$needle%")
                    ->orWhere('fiscal_full_number', 'like', "%$needle%")
                    ->orWhere('pickup_number', 'like', "%$needle%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $orders = $query->paginate($perPage);

        return response()->json([
            'orders' => collect($orders->items())->map(fn(Order $o) => $this->mapOrderSummary($o))->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function showOrder(int $id): JsonResponse
    {
        $order = Order::with(['worker:id,name', 'items.product:id,name'])
            ->findOrFail($id);

        return response()->json([
            'order' => array_merge($this->mapOrderSummary($order), [
                'items' => $order->items->map(function (OrderItem $item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->name ?? '(Producte eliminat)',
                        'quantity' => (int) $item->quantity,
                        'price' => (float) ($item->price_at_sale ?? 0),
                        'subtotal' => (float) (($item->price_at_sale ?? 0) * $item->quantity),
                    ];
                })->values(),
            ]),
        ]);
    }

    public function destroyOrder(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['ok' => true]);
    }

    protected function mapOrderSummary(Order $o): array
    {
        return [
            'id' => $o->id,
            'total_price' => (float) $o->total_price,
            'payment_method' => $o->payment_method,
            'status' => $o->status,
            'is_preorder' => (bool) $o->is_preorder,
            'pickup_number' => $o->pickup_number,
            'pickup_time' => $o->pickup_time,
            'customer_name' => $o->customer_name,
            'fiscal_full_number' => $o->fiscal_full_number,
            'worker_id' => $o->worker_id,
            'worker_name' => $o->worker?->name,
            'items_count' => (int) $o->items->sum('quantity'),
            'created_at' => $o->created_at?->toIso8601String(),
        ];
    }

    // --- Workers CRUD ---
    public function listWorkers(): JsonResponse
    {
        $workers = Worker::withCount('orders')
            ->orderBy('name')
            ->get();

        return response()->json([
            'workers' => $workers->map(fn(Worker $w) => $this->mapWorker($w))->values(),
        ]);
    }

    public function storeWorker(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'pin' => 'nullable|string|size:4|unique:workers,pin',
            'active' => 'nullable|boolean',
        ]);

        $worker = new Worker();
        $worker->name = $data['name'];
        $worker->pin = !empty($data['pin']) ? $data['pin'] : null;
        $worker->active = array_key_exists('active', $data) ? (bool) $data['active'] : true;
        $worker->save();

        return response()->json([
            'ok' => true,
            'worker' => $this->mapWorker($worker->loadCount('orders')),
        ], 201);
    }

    public function updateWorker(Request $request, int $id): JsonResponse
    {
        $worker = Worker::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'pin' => 'nullable|string|size:4|unique:workers,pin,' . $worker->id,
            'active' => 'nullable|boolean',
        ]);

        $worker->name = $data['name'];
        $worker->pin = !empty($data['pin']) ? $data['pin'] : null;
        if (array_key_exists('active', $data)) {
            $worker->active = (bool) $data['active'];
        }
        $worker->save();

        return response()->json([
            'ok' => true,
            'worker' => $this->mapWorker($worker->loadCount('orders')),
        ]);
    }

    public function destroyWorker(int $id): JsonResponse
    {
        $worker = Worker::findOrFail($id);
        $worker->delete();
        return response()->json(['ok' => true]);
    }

    protected function mapWorker(Worker $w): array
    {
        return [
            'id' => $w->id,
            'name' => $w->name,
            'has_pin' => !empty($w->pin),
            'pin' => $w->pin,
            'active' => (bool) ($w->active ?? true),
            'orders_count' => (int) ($w->orders_count ?? 0),
        ];
    }

    protected function mapProduct(Product $p): array
    {
        $category = $p->categories->first();
        return [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (float) $p->price,
            'stock' => (int) ($p->stock ?? 0),
            'is_gluten_free' => (bool) $p->is_gluten_free,
            'description' => $p->description,
            'image_path' => $p->image_path,
            'active' => (bool) $p->active,
            'category_id' => $category?->id,
            'category_name' => $category?->name,
            'category_color' => $category?->color,
        ];
    }
}
