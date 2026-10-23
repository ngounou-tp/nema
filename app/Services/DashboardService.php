<?php

namespace App\Services;

use App\Enums\MessageRole;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\AiMessage;
use App\Models\Diagnosis;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Figures of each role's dashboard, read from the database. Values stay raw
 * (enum keys, numbers, ISO dates): the interface translates and formats them.
 */
class DashboardService
{
    private const int LIST_SIZE = 5;

    /**
     * @return array<string, mixed>
     */
    public function forProducer(User $user): array
    {
        $producer = $user->producer;

        if ($producer === null) {
            return ['products' => ['total' => 0, 'available' => 0, 'latest' => []], 'ordersInProgress' => [], 'score' => null, 'diagnoses' => []];
        }

        $products = $producer->products();

        return [
            'products' => [
                'total' => $products->count(),
                'available' => $producer->products()->where('is_available', true)->count(),
                'latest' => $producer->products()->latest()->limit(self::LIST_SIZE)->get()
                    ->map(fn (Product $product): array => $this->product($product))->all(),
            ],
            'ordersInProgress' => $producer->orders()
                ->whereIn('orders.status', [OrderStatus::Pending, OrderStatus::Confirmed])
                ->with(['product:id,name,filiere', 'buyer:id,name'])
                ->latest('orders.created_at')
                ->get()
                ->map(fn (Order $order): array => $this->order($order, counterpart: $order->buyer->name))
                ->all(),
            'score' => $producer->supplierScore === null ? null : [
                'value' => (int) round((float) $producer->supplierScore->score),
                'computedAt' => $producer->supplierScore->computed_at->toIso8601String(),
            ],
            'diagnoses' => $user->diagnoses()->latest()->limit(3)->get()
                ->map(fn (Diagnosis $diagnosis): array => [
                    'id' => $diagnosis->id,
                    'crop' => $diagnosis->crop,
                    'status' => $diagnosis->status->value,
                    'confidence' => $diagnosis->confidence === null ? null : (float) $diagnosis->confidence,
                    'createdAt' => $diagnosis->created_at?->toIso8601String(),
                ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forCooperative(User $user): array
    {
        $cooperative = $user->managedCooperative;

        if ($cooperative === null) {
            return ['cooperative' => null, 'members' => ['total' => 0, 'latest' => []], 'stock' => [], 'ordersThisMonth' => ['count' => 0, 'totalXaf' => 0, 'latest' => []]];
        }

        $memberIds = $cooperative->producers()->pluck('id');
        $memberOrders = fn (): Builder => Order::query()
            ->whereHas('product', fn (Builder $query) => $query->whereIn('producer_id', $memberIds))
            ->where('created_at', '>=', now()->startOfMonth());

        return [
            'cooperative' => ['name' => $cooperative->name, 'region' => $cooperative->region->value],
            'members' => [
                'total' => $memberIds->count(),
                'latest' => $cooperative->producers()->with('user:id,name')->latest()->limit(self::LIST_SIZE)->get()
                    ->map(fn (Producer $producer): array => [
                        'id' => $producer->id,
                        'name' => $producer->user->name,
                        'village' => $producer->village,
                        'surfaceHa' => (float) $producer->surface_ha,
                    ])->all(),
            ],
            'stock' => Product::query()
                ->whereIn('producer_id', $memberIds)
                ->where('is_available', true)
                ->selectRaw('filiere, sum(quantity_kg) as quantity_kg')
                ->groupBy('filiere')
                ->orderByDesc('quantity_kg')
                ->toBase()
                ->get()
                ->map(fn (object $row): array => ['filiere' => $row->filiere, 'quantityKg' => (float) $row->quantity_kg])
                ->all(),
            'ordersThisMonth' => [
                'count' => $memberOrders()->count(),
                'totalXaf' => (int) $memberOrders()->where('status', '!=', OrderStatus::Cancelled)->sum('total_xaf'),
                'latest' => $memberOrders()->with(['product:id,name,filiere', 'buyer:id,name'])->latest()->limit(self::LIST_SIZE)->get()
                    ->map(fn (Order $order): array => $this->order($order, counterpart: $order->buyer->name))->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forBuyer(User $user): array
    {
        return [
            'ordersInProgress' => $user->orders()->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed])->count(),
            'recentOrders' => $user->orders()->with(['product:id,name,filiere,producer_id', 'product.producer.user:id,name'])
                ->latest()->limit(self::LIST_SIZE)->get()
                ->map(fn (Order $order): array => $this->order($order, counterpart: $order->product->producer->user->name))->all(),
            'topSuppliers' => $this->topRatedSuppliers(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forAdmin(): array
    {
        $usersByRole = User::query()->selectRaw('role, count(*) as total')->groupBy('role')->toBase()->pluck('total', 'role');
        $ordersByStatus = Order::query()->selectRaw('status, count(*) as total')->groupBy('status')->toBase()->pluck('total', 'status');

        return [
            'users' => [
                'total' => (int) $usersByRole->sum(),
                'byRole' => collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [$role->value => (int) ($usersByRole[$role->value] ?? 0)])->all(),
            ],
            'orders' => [
                'total' => (int) $ordersByStatus->sum(),
                'byStatus' => collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status): array => [$status->value => (int) ($ordersByStatus[$status->value] ?? 0)])->all(),
            ],
            'aiCalls' => $this->aiCallsLastSevenDays(),
        ];
    }

    /**
     * Best average review rating (at least 3 reviews), from real orders.
     *
     * @return list<array<string, mixed>>
     */
    private function topRatedSuppliers(): array
    {
        $ratings = Review::query()
            ->join('orders', 'orders.id', '=', 'reviews.order_id')
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->selectRaw('products.producer_id, avg(reviews.rating) as average_rating, count(*) as reviews_count')
            ->groupBy('products.producer_id')
            ->havingRaw('count(*) >= 3')
            ->orderByDesc('average_rating')
            ->orderByDesc('reviews_count')
            ->limit(self::LIST_SIZE)
            ->toBase()
            ->get();

        $producers = Producer::query()->with(['user:id,name', 'cooperative:id,region', 'supplierScore'])
            ->findMany($ratings->pluck('producer_id'))->keyBy('id');

        return array_values($ratings->map(function (object $rating) use ($producers): array {
            $producer = $producers[$rating->producer_id];

            return [
                'id' => $producer->id,
                'name' => $producer->user->name,
                'village' => $producer->village,
                'region' => $producer->cooperative?->region->value,
                'averageRating' => round((float) $rating->average_rating, 1),
                'reviewsCount' => (int) $rating->reviews_count,
                'score' => $producer->supplierScore === null ? null : (int) round((float) $producer->supplierScore->score),
            ];
        })->all());
    }

    /**
     * Assistant answers per day, today included.
     *
     * @return array{total: int, days: list<array{date: string, count: int}>}
     */
    private function aiCallsLastSevenDays(): array
    {
        $since = now()->subDays(6)->startOfDay();

        $counts = AiMessage::query()
            ->where('role', MessageRole::Assistant)
            ->where('created_at', '>=', $since)
            ->pluck('created_at')
            ->countBy(fn (CarbonInterface $createdAt): string => $createdAt->toDateString());

        $days = array_map(function (int $offset) use ($since, $counts): array {
            $date = $since->copy()->addDays($offset)->toDateString();

            return ['date' => $date, 'count' => (int) ($counts[$date] ?? 0)];
        }, range(0, 6));

        return ['total' => array_sum(array_column($days, 'count')), 'days' => $days];
    }

    /**
     * @return array<string, mixed>
     */
    private function product(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'filiere' => $product->filiere->value,
            'quantityKg' => (float) $product->quantity_kg,
            'priceXaf' => $product->price_xaf,
            'isAvailable' => $product->is_available,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function order(Order $order, string $counterpart): array
    {
        return [
            'id' => $order->id,
            'product' => $order->product->name,
            'filiere' => $order->product->filiere->value,
            'counterpart' => $counterpart,
            'quantityKg' => (float) $order->quantity_kg,
            'totalXaf' => $order->total_xaf,
            'status' => $order->status->value,
            'createdAt' => $order->created_at?->toIso8601String(),
        ];
    }
}
