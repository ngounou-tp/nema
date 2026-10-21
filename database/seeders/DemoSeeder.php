<?php

namespace Database\Seeders;

use App\Enums\Filiere;
use App\Enums\Locale;
use App\Enums\OrderStatus;
use App\Enums\QualityGrade;
use App\Enums\UserRole;
use App\Models\Cooperative;
use App\Models\Lot;
use App\Models\MarketPrice;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Realistic demo data for Cameroon (jury demo of 25 October 2026).
 *
 * Idempotent: it first removes the previous demo data (accounts on the
 * nema.demo domain and everything attached to them, demo market prices),
 * so it can run after `migrate:fresh` or on its own.
 */
class DemoSeeder extends Seeder
{
    public const string PASSWORD = 'nema2026';

    public const string EMAIL_DOMAIN = 'nema.demo';

    /**
     * Demo accounts shown on the home page ("Essayer en tant que…").
     *
     * @var array<string, string>
     */
    public const array DEMO_ACCOUNTS = [
        'producer' => 'producteur@nema.demo',
        'cooperative' => 'coop@nema.demo',
        'buyer' => 'acheteur@nema.demo',
        'admin' => 'admin@nema.demo',
    ];

    private const string PRICES_FROM = '2025-11-01';

    private const int PRICE_MONTHS = 12;

    /**
     * Cooperatives, their villages and the filières grown there.
     *
     * @var array<string, array{name: string, towns: list<string>, manager: string, manager_email: string, filieres: list<Filiere>, villages: list<string>, producers: int}>
     */
    private const array COOPERATIVES = [
        'Ouest' => [
            'name' => 'Coopérative Agricole des Hauts-Plateaux de la Menoua (CAHPM)',
            'towns' => ['Dschang', 'Bafoussam'],
            'manager' => 'Hélène Kenfack',
            'manager_email' => 'gerant.ouest@nema.demo',
            'filieres' => [Filiere::CafeArabica, Filiere::Mais, Filiere::Tomate, Filiere::Igname, Filiere::Plantain],
            'villages' => ['Foto', 'Fongo-Tongo', 'Baleveng', 'Bafou', 'Nkong-Ni', 'Bamendjou', 'Baham', 'Bandjoun', 'Penka-Michel'],
            'producers' => 9,
        ],
        'Littoral' => [
            'name' => 'Société Coopérative des Planteurs du Moungo (SCPM)',
            'towns' => ['Penja', 'Njombé'],
            'manager' => 'Paul Ekwalla',
            'manager_email' => 'gerant.littoral@nema.demo',
            'filieres' => [Filiere::Banane, Filiere::Plantain, Filiere::CafeRobusta, Filiere::Cacao, Filiere::HuileDePalme],
            'villages' => ['Penja', 'Njombé', 'Loum', 'Manjo', 'Mbanga', 'Nlonako', 'Kompina'],
            'producers' => 8,
        ],
        'Centre' => [
            'name' => 'Coopérative des Producteurs de la Lékié et du Nyong (COPLEN)',
            'towns' => ['Obala', 'Mbalmayo'],
            'manager' => 'Bernadette Ngono',
            'manager_email' => self::DEMO_ACCOUNTS['cooperative'],
            'filieres' => [Filiere::Cacao, Filiere::Manioc, Filiere::Arachide, Filiere::Plantain, Filiere::Mais, Filiere::HuileDePalme],
            'villages' => ['Obala', 'Mbalmayo', 'Nkolmetet', "Sa'a", 'Okola', 'Ngomedzap', 'Elig-Mfomo', 'Batschenga'],
            'producers' => 8,
        ],
    ];

    /**
     * Credible first names and surnames per region.
     *
     * @var array<string, array{first: list<string>, last: list<string>}>
     */
    private const array NAMES = [
        'Ouest' => [
            'first' => ['Jean-Pierre', 'Marie-Claire', 'Emmanuel', 'Odile', 'Hervé', 'Christelle', 'Joseph', 'Pauline', 'Samuel', 'Thérèse', 'Gaston', 'Josiane'],
            'last' => ['Fotso', 'Kamga', 'Tchinda', 'Djoumessi', 'Tagne', 'Wamba', 'Nguimfack', 'Talla', 'Nzeyang', 'Feudjio', 'Momo', 'Tsafack'],
        ],
        'Littoral' => [
            'first' => ['Rose', 'Didier', 'Clarisse', 'Martin', 'Alain', 'Félicité', 'Serge', 'Pélagie', 'Jacqueline', 'Roger', 'Esther'],
            'last' => ['Ebongue', 'Moukouri', 'Ndoumbe', 'Epee', 'Kotto', 'Dikongue', 'Essombe', 'Nkotto', 'Mbella', 'Din', 'Eyoum'],
        ],
        'Centre' => [
            'first' => ['Jacques', 'Brigitte', 'François', 'Yvonne', 'Cyrille', 'Agnès', 'Thomas', 'Solange', 'Basile', 'Mireille', 'Désiré'],
            'last' => ['Atangana', 'Mbarga', 'Essomba', 'Owona', 'Abena', 'Onana', 'Manga', 'Ndzana', 'Mvondo', 'Eyenga', 'Bella'],
        ],
    ];

    /**
     * Reference farm-gate price (FCFA/kg, October 2026), typical lot size (kg) and product names.
     *
     * @var array<string, array{price: int, quantity: array{int, int}, names: list<string>, description: string}>
     */
    private const array FILIERES = [
        'cacao' => ['price' => 4100, 'quantity' => [300, 3000], 'names' => ['Cacao marchand fermenté', 'Fèves de cacao séchées grade I'], 'description' => 'Fèves fermentées 6 jours en bacs et séchées au soleil, humidité inférieure à 8 %.'],
        'cafe_arabica' => ['price' => 2500, 'quantity' => [200, 2000], 'names' => ['Café arabica parche', 'Café arabica vert lavé'], 'description' => "Arabica d'altitude cueilli à maturité, dépulpé, lavé et séché sur claies."],
        'cafe_robusta' => ['price' => 1800, 'quantity' => [300, 2500], 'names' => ['Café robusta coque', 'Café robusta marchand'], 'description' => 'Robusta séché en coque puis décortiqué, sacs de jute de 60 kg.'],
        'mais' => ['price' => 280, 'quantity' => [500, 8000], 'names' => ['Maïs grain jaune sec', 'Maïs blanc séché'], 'description' => 'Maïs égrené et séché, sans charançons, livré en sacs de 100 kg.'],
        'manioc' => ['price' => 200, 'quantity' => [300, 4000], 'names' => ['Cossettes de manioc', 'Tubercules de manioc frais'], 'description' => 'Manioc doux récolté du jour ou transformé en cossettes séchées.'],
        'plantain' => ['price' => 300, 'quantity' => [200, 2500], 'names' => ['Régimes de plantain', 'Plantain mûr'], 'description' => 'Régimes bien remplis, récoltés à maturité commerciale.'],
        'banane' => ['price' => 250, 'quantity' => [300, 3000], 'names' => ['Banane douce dessert', 'Banane Grande Naine'], 'description' => 'Bananes dessert calibrées, mûrissement contrôlé.'],
        'tomate' => ['price' => 450, 'quantity' => [100, 1500], 'names' => ['Tomates fraîches en cageots', 'Tomates Roma'], 'description' => 'Tomates fermes récoltées tournantes, conditionnées en cageots de 25 kg.'],
        'arachide' => ['price' => 850, 'quantity' => [100, 1200], 'names' => ['Arachides décortiquées', 'Arachides en coque'], 'description' => 'Arachides triées à la main, bien sèches, sans moisissures.'],
        'igname' => ['price' => 380, 'quantity' => [200, 2500], 'names' => ['Igname blanche', 'Igname jaune'], 'description' => 'Tubercules sains, récoltés et stockés à l’abri.'],
        'huile_de_palme' => ['price' => 1050, 'quantity' => [50, 800], 'names' => ['Huile de palme rouge artisanale', 'Huile de palme brute'], 'description' => 'Huile rouge pressée localement, bidons de 20 litres.'],
    ];

    /**
     * Monthly seasonal multipliers, November 2025 → October 2026.
     *
     * @var array<string, list<float>>
     */
    private const array SEASONALITY = [
        'cacao' => [1.12, 1.10, 1.07, 1.05, 1.03, 1.01, 0.99, 0.98, 0.97, 0.98, 0.99, 1.00],
        'cafe_arabica' => [0.96, 0.95, 0.96, 0.98, 1.00, 1.01, 1.02, 1.03, 1.04, 1.03, 1.01, 1.00],
        'cafe_robusta' => [0.95, 0.94, 0.96, 0.98, 1.00, 1.02, 1.03, 1.04, 1.03, 1.02, 1.01, 1.00],
        'mais' => [0.90, 0.85, 0.90, 1.00, 1.10, 1.20, 1.25, 1.15, 0.95, 0.85, 0.88, 0.92],
        'manioc' => [0.95, 0.92, 0.95, 1.02, 1.10, 1.15, 1.18, 1.10, 1.00, 0.95, 0.94, 0.96],
        'plantain' => [0.92, 0.95, 1.00, 1.08, 1.15, 1.20, 1.12, 1.02, 0.95, 0.90, 0.92, 0.95],
        'banane' => [0.98, 1.00, 1.02, 1.03, 1.04, 1.03, 1.01, 0.99, 0.98, 0.97, 0.98, 1.00],
        'tomate' => [0.85, 0.75, 0.70, 0.72, 0.85, 1.00, 1.15, 1.30, 1.40, 1.35, 1.20, 0.95],
        'arachide' => [0.95, 0.98, 1.02, 1.08, 1.15, 1.20, 1.12, 0.95, 0.85, 0.88, 0.92, 0.96],
        'igname' => [0.90, 0.92, 0.98, 1.05, 1.12, 1.18, 1.15, 1.05, 0.95, 0.88, 0.88, 0.92],
        'huile_de_palme' => [1.08, 1.10, 1.05, 0.98, 0.92, 0.90, 0.92, 0.96, 1.00, 1.04, 1.06, 1.08],
    ];

    /**
     * Regional price level (Douala and Yaoundé markets are dearer than the Ouest).
     *
     * @var array<string, float>
     */
    private const array REGION_PRICE_FACTOR = [
        'Ouest' => 0.95,
        'Littoral' => 1.06,
        'Centre' => 1.02,
    ];

    /**
     * Reliability profiles that drive deliveries and reviews, so that future supplier scores are contrasted.
     *
     * @var array<string, array{on_time: float, rating: array{int, int}, quality: array{int, int}, complaint: float, seniority: array{int, int}}>
     */
    private const array PROFILES = [
        'excellent' => ['on_time' => 0.95, 'rating' => [4, 5], 'quality' => [4, 5], 'complaint' => 0.02, 'seniority' => [18, 36]],
        'good' => ['on_time' => 0.82, 'rating' => [3, 5], 'quality' => [3, 5], 'complaint' => 0.08, 'seniority' => [10, 30]],
        'average' => ['on_time' => 0.65, 'rating' => [2, 4], 'quality' => [2, 4], 'complaint' => 0.18, 'seniority' => [5, 20]],
        'poor' => ['on_time' => 0.45, 'rating' => [1, 3], 'quality' => [1, 3], 'complaint' => 0.35, 'seniority' => [2, 8]],
    ];

    /**
     * Additional buyers (companies and individuals) besides the demo buyer.
     *
     * @var list<array{name: string, email: string, region: string, locale?: Locale}>
     */
    private const array BUYERS = [
        ['name' => 'Douala Cocoa Processing SARL', 'email' => 'achats.dcp@nema.demo', 'region' => 'Littoral'],
        ['name' => 'Agro-Export Wouri SA', 'email' => 'contact.agroexportwouri@nema.demo', 'region' => 'Littoral', 'locale' => Locale::English],
        ['name' => 'Supermarché Le Bon Panier', 'email' => 'appro.bonpanier@nema.demo', 'region' => 'Centre'],
        ['name' => 'Restaurant Saveurs du Mfoundi', 'email' => 'saveurs.mfoundi@nema.demo', 'region' => 'Centre'],
        ['name' => 'Boulangerie-Pâtisserie La Mie Dorée', 'email' => 'miedoree@nema.demo', 'region' => 'Littoral'],
        ['name' => 'Torréfaction des Hauts-Plateaux', 'email' => 'torrefaction.hp@nema.demo', 'region' => 'Ouest'],
        ['name' => 'Régine Etoundi (revendeuse, marché Mokolo)', 'email' => 'regine.etoundi@nema.demo', 'region' => 'Centre'],
        ['name' => 'Christian Nkodo', 'email' => 'christian.nkodo@nema.demo', 'region' => 'Centre', 'locale' => Locale::English],
        ['name' => 'Aïcha Bello', 'email' => 'aicha.bello@nema.demo', 'region' => 'Littoral'],
        ['name' => 'Ginette Mbida (revendeuse, marché Sandaga)', 'email' => 'ginette.mbida@nema.demo', 'region' => 'Littoral'],
    ];

    /**
     * Review comments, from satisfied to dissatisfied buyers.
     *
     * @var array<string, list<string>>
     */
    private const array COMMENTS = [
        'positive' => [
            'Livraison rapide et produit conforme à l’annonce. Je recommande.',
            'Très bonne qualité, marchandise propre et bien emballée.',
            'Producteur sérieux, deuxième commande sans aucun souci.',
            'Poids exact et produit bien sec. Rien à redire.',
            'Bonne communication, livré au jour prévu.',
        ],
        'neutral' => [
            'Qualité correcte mais livraison avec quelques jours de retard.',
            'Quelques sacs un peu humides, sinon ça va.',
            'Bon produit, la communication peut être améliorée.',
            'Conforme dans l’ensemble, calibrage un peu irrégulier.',
        ],
        'negative' => [
            'Livraison très en retard, sans prévenir.',
            'Poids inférieur à la commande : sacs de 45 kg au lieu de 50.',
            'Taux d’humidité trop élevé, une partie du lot a moisi.',
            'Produit différent de l’annonce, qualité décevante.',
        ],
    ];

    private string $passwordHash;

    /**
     * Seed the demo data.
     */
    public function run(): void
    {
        $this->passwordHash = Hash::make(self::PASSWORD);

        DB::transaction(function (): void {
            $this->purge();

            $this->createAccount('Mme Sandrine Abega', self::DEMO_ACCOUNTS['admin'], UserRole::Admin, 'Centre');

            $cooperatives = $this->createCooperatives();
            $producers = $this->createProducers($cooperatives);
            $products = $this->createProducts($producers);
            $buyers = $this->createBuyers();

            $this->createOrders($producers, $products, $buyers);
            $this->createLots($producers, $products);
            $this->createMarketPrices();
        });
    }

    /**
     * Remove previous demo data. Cascading foreign keys remove the producers,
     * products, lots, orders, reviews and AI data attached to demo accounts.
     */
    private function purge(): void
    {
        $demoUsers = User::query()->where('email', 'like', '%@'.self::EMAIL_DOMAIN);

        Cooperative::query()->whereIn('manager_id', (clone $demoUsers)->select('id'))->delete();
        $demoUsers->delete();

        MarketPrice::query()
            ->whereBetween('date', [self::PRICES_FROM, $this->priceMonth(self::PRICE_MONTHS - 1)->toDateString()])
            ->delete();
    }

    private function createAccount(string $name, string $email, UserRole $role, string $region, Locale $locale = Locale::French): User
    {
        return User::query()->forceCreate([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => $this->passwordHash,
            'role' => $role,
            'region' => $region,
            'phone' => $this->phone(),
            'locale' => $locale,
        ]);
    }

    /**
     * @return Collection<string, Cooperative>
     */
    private function createCooperatives(): Collection
    {
        return collect(self::COOPERATIVES)->map(function (array $definition, string $region): Cooperative {
            $manager = $this->createAccount($definition['manager'], $definition['manager_email'], UserRole::Cooperative, $region);

            return Cooperative::query()->create([
                'name' => $definition['name'],
                'region' => $region,
                'filieres' => array_map(fn (Filiere $filiere): string => $filiere->value, $definition['filieres']),
                'manager_id' => $manager->id,
            ]);
        });
    }

    /**
     * Create 25 producers. The demo producer is a cacao farmer of Obala with an
     * excellent track record (jury flow: diagnosis of a cacao leaf, then sale).
     *
     * @param  Collection<string, Cooperative>  $cooperatives
     * @return Collection<int, array{producer: Producer, region: string, profile: key-of<self::PROFILES>}>
     */
    private function createProducers(Collection $cooperatives): Collection
    {
        $profiles = collect(array_merge(
            array_fill(0, 5, 'excellent'),
            array_fill(0, 8, 'good'),
            array_fill(0, 6, 'average'),
            array_fill(0, 5, 'poor'),
        ))->shuffle()->values();

        $producers = collect();
        $usedNames = [];

        foreach (self::COOPERATIVES as $region => $definition) {
            for ($index = 0; $index < $definition['producers']; $index++) {
                $isDemoProducer = $region === 'Centre' && $index === 0;
                $profile = $isDemoProducer ? 'excellent' : ($profiles->shift() ?? 'average');

                if ($isDemoProducer) {
                    $name = 'Emmanuel Mbarga';
                    $email = self::DEMO_ACCOUNTS['producer'];
                    $village = 'Obala';
                } else {
                    $name = $this->uniqueName($region, $usedNames);
                    $email = Str::slug($name, '.').'@'.self::EMAIL_DOMAIN;
                    $village = fake()->randomElement($definition['villages']);
                }

                $usedNames[] = $name;
                $user = $this->createAccount($name, $email, UserRole::Producer, $region);

                // The last producer of each region sells independently, outside any cooperative.
                $isIndependent = $index === $definition['producers'] - 1;
                [$minMonths, $maxMonths] = self::PROFILES[$profile]['seniority'];
                $joinedAt = now()->subMonths($isDemoProducer ? 26 : fake()->numberBetween($minMonths, $maxMonths));

                $producer = Producer::query()->forceCreate([
                    'user_id' => $user->id,
                    'cooperative_id' => $isIndependent ? null : $cooperatives[$region]->id,
                    'village' => $village,
                    'surface_ha' => $isDemoProducer ? 6.5 : fake()->randomFloat(2, 0.5, 12),
                    'created_at' => $joinedAt,
                    'updated_at' => $joinedAt,
                ]);

                $producers->push(['producer' => $producer, 'region' => $region, 'profile' => $profile]);
            }
        }

        return $producers;
    }

    /**
     * Create 60 products: 3 for the first 10 producers, 2 for the others.
     *
     * @param  Collection<int, array{producer: Producer, region: string, profile: key-of<self::PROFILES>}>  $producers
     * @return Collection<int, Product>
     */
    private function createProducts(Collection $producers): Collection
    {
        $products = collect();

        foreach ($producers->values() as $position => $entry) {
            $producer = $entry['producer'];
            $count = $position < 10 ? 3 : 2;
            $filieres = collect(self::COOPERATIVES[$entry['region']]['filieres'])->shuffle();

            if ($producer->user->email === self::DEMO_ACCOUNTS['producer']) {
                $filieres = collect([Filiere::Cacao, Filiere::Plantain, Filiere::Arachide]);
            }

            foreach ($filieres->take($count) as $filiere) {
                $definition = self::FILIERES[$filiere->value];
                $grade = [QualityGrade::A, QualityGrade::A, QualityGrade::B, QualityGrade::B, QualityGrade::C][fake()->numberBetween(0, 4)];
                $gradeFactor = match ($grade) {
                    QualityGrade::A => 1.08,
                    QualityGrade::B => 1.0,
                    QualityGrade::C => 0.9,
                };

                $products->push(Product::query()->create([
                    'producer_id' => $producer->id,
                    'filiere' => $filiere,
                    'name' => fake()->randomElement($definition['names']).' – '.$producer->village,
                    'description' => $definition['description'],
                    'quantity_kg' => $this->roundTo(fake()->numberBetween(...$definition['quantity']), 10),
                    'price_xaf' => $this->roundTo($definition['price'] * self::REGION_PRICE_FACTOR[$entry['region']] * $gradeFactor * fake()->randomFloat(2, 0.94, 1.06), 5),
                    'quality_grade' => $grade,
                    'is_available' => $producer->user->email === self::DEMO_ACCOUNTS['producer'] || fake()->boolean(90),
                ]));
            }
        }

        return $products;
    }

    /**
     * @return Collection<int, User>
     */
    private function createBuyers(): Collection
    {
        $buyers = collect([$this->createAccount('Arlette Ndjock', self::DEMO_ACCOUNTS['buyer'], UserRole::Buyer, 'Littoral')]);

        foreach (self::BUYERS as $buyer) {
            $buyers->push($this->createAccount($buyer['name'], $buyer['email'], UserRole::Buyer, $buyer['region'], $buyer['locale'] ?? Locale::French));
        }

        return $buyers;
    }

    /**
     * Create 120 orders over the last 12 months. Every producer gets at least
     * 2 orders; the demo producer gets 9 delivered orders with good reviews.
     *
     * @param  Collection<int, array{producer: Producer, region: string, profile: key-of<self::PROFILES>}>  $producers
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, User>  $buyers
     */
    private function createOrders(Collection $producers, Collection $products, Collection $buyers): void
    {
        $productsByProducer = $products->groupBy('producer_id');
        $profileByProducer = $producers->mapWithKeys(fn (array $entry): array => [$entry['producer']->id => self::PROFILES[$entry['profile']]]);
        $demoProducerId = $producers->first(fn (array $entry): bool => $entry['producer']->user->email === self::DEMO_ACCOUNTS['producer'])['producer']->id;

        $orderedProducts = collect();

        foreach ($productsByProducer as $producerId => $producerProducts) {
            $count = $producerId === $demoProducerId ? 9 : 2;

            for ($index = 0; $index < $count; $index++) {
                $orderedProducts->push($producerProducts->random());
            }
        }

        while ($orderedProducts->count() < 120) {
            $product = $products->random();

            if ($product->producer_id !== $demoProducerId) {
                $orderedProducts->push($product);
            }
        }

        foreach ($orderedProducts as $product) {
            $isDemoProducer = $product->producer_id === $demoProducerId;
            $this->createOrder($product, $buyers->random(), $profileByProducer[$product->producer_id], $isDemoProducer);
        }
    }

    /**
     * @param  array{on_time: float, rating: array{int, int}, quality: array{int, int}, complaint: float, seniority: array{int, int}}  $profile
     */
    private function createOrder(Product $product, User $buyer, array $profile, bool $isDemoProducer): void
    {
        $status = $isDemoProducer ? OrderStatus::Delivered : fake()->randomElement([
            ...array_fill(0, 15, OrderStatus::Delivered),
            OrderStatus::Confirmed,
            OrderStatus::Confirmed,
            OrderStatus::Pending,
            OrderStatus::Pending,
            OrderStatus::Cancelled,
        ]);

        $orderedAt = match ($status) {
            OrderStatus::Pending, OrderStatus::Confirmed => now()->subDays(fake()->numberBetween(1, 14)),
            default => Carbon::instance(fake()->dateTimeBetween('-11 months', '-3 weeks')),
        };

        $quantityKg = $this->roundTo(max(20, (float) $product->quantity_kg * fake()->randomFloat(2, 0.05, 0.3)), 5);
        $onTime = $isDemoProducer || fake()->randomFloat(2, 0, 1) < $profile['on_time'];
        $isDelivered = $status === OrderStatus::Delivered;

        $order = Order::query()->forceCreate([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity_kg' => $quantityKg,
            'total_xaf' => (int) round($quantityKg * $product->price_xaf),
            'status' => $status,
            'delivered_on_time' => $isDelivered ? $onTime : null,
            'delivered_at' => $isDelivered ? $orderedAt->copy()->addDays($onTime ? fake()->numberBetween(2, 6) : fake()->numberBetween(9, 21)) : null,
            'created_at' => $orderedAt,
            'updated_at' => $orderedAt,
        ]);

        if ($isDelivered) {
            $this->createReview($order, $profile, $onTime, $isDemoProducer);
        }
    }

    /**
     * @param  array{on_time: float, rating: array{int, int}, quality: array{int, int}, complaint: float, seniority: array{int, int}}  $profile
     */
    private function createReview(Order $order, array $profile, bool $onTime, bool $isDemoProducer): void
    {
        $hasComplaint = ! $isDemoProducer && fake()->randomFloat(2, 0, 1) < $profile['complaint'];
        $rating = fake()->numberBetween(...$profile['rating']) - ($onTime ? 0 : 1) - ($hasComplaint ? 1 : 0);
        $rating = max(1, min(5, $rating));
        $qualityRating = max(1, min(5, fake()->numberBetween(...$profile['quality']) - ($hasComplaint ? 1 : 0)));

        $tone = match (true) {
            $hasComplaint || $rating <= 2 => 'negative',
            $rating === 3 || ! $onTime => 'neutral',
            default => 'positive',
        };

        $reviewedAt = $order->delivered_at?->copy()->addDays(fake()->numberBetween(0, 3));

        Review::query()->forceCreate([
            'order_id' => $order->id,
            'rating' => $rating,
            'quality_rating' => $qualityRating,
            'comment' => fake()->boolean(85) ? fake()->randomElement(self::COMMENTS[$tone]) : null,
            'has_complaint' => $hasComplaint,
            'created_at' => $reviewedAt,
            'updated_at' => $reviewedAt,
        ]);
    }

    /**
     * Create 20 traceable lots with 4 to 6 steps, two of them for the demo producer's cacao.
     *
     * @param  Collection<int, array{producer: Producer, region: string, profile: key-of<self::PROFILES>}>  $producers
     * @param  Collection<int, Product>  $products
     */
    private function createLots(Collection $producers, Collection $products): void
    {
        $regionByProducer = $producers->mapWithKeys(fn (array $entry): array => [$entry['producer']->id => $entry['region']]);
        $demoCacao = $products->first(fn (Product $product): bool => $product->filiere === Filiere::Cacao
            && $product->producer->user->email === self::DEMO_ACCOUNTS['producer']);

        $lotProducts = collect([$demoCacao, $demoCacao])
            ->merge($products->reject(fn (Product $product): bool => $product->is($demoCacao))->shuffle()->take(18));

        foreach ($lotProducts as $product) {
            $region = $regionByProducer[$product->producer_id];
            $harvestDate = Carbon::instance(fake()->dateTimeBetween('-4 months', '-2 weeks'))->startOfDay();

            Lot::query()->create([
                'product_id' => $product->id,
                'harvest_date' => $harvestDate,
                'origin' => $product->producer->village.', région '.($region === 'Ouest' ? 'de l’Ouest' : 'du '.$region),
                'steps' => $this->traceabilitySteps($product, $region, $harvestDate),
            ]);
        }
    }

    /**
     * @return list<array{label: string, date: string, location: string}>
     */
    private function traceabilitySteps(Product $product, string $region, Carbon $harvestDate): array
    {
        $village = $product->producer->village;
        $town = fake()->randomElement(self::COOPERATIVES[$region]['towns']);
        $warehouse = $product->producer->cooperative_id === null ? 'Magasin du producteur, '.$village : 'Magasin de la coopérative, '.$town;

        $steps = match ($product->filiere) {
            Filiere::Cacao => [
                ['Récolte et écabossage', 0, 'Parcelle du producteur, '.$village],
                ['Fermentation en bacs (6 jours)', 1, 'Unité de fermentation, '.$village],
                ['Séchage au soleil', 7, 'Aire de séchage, '.$village],
                ['Tri et ensachage', 15, $warehouse],
                ['Contrôle qualité (humidité < 8 %)', 17, $warehouse],
                ['Expédition vers le port de Douala', 20, 'Port autonome de Douala'],
            ],
            Filiere::CafeArabica, Filiere::CafeRobusta => [
                ['Cueillette des cerises', 0, 'Plantation, '.$village],
                ['Dépulpage et lavage', 1, 'Station de lavage, '.$village],
                ['Séchage sur claies', 3, 'Aire de séchage, '.$village],
                ['Décorticage et triage', 18, $warehouse],
                ['Ensachage (sacs de 60 kg)', 20, $warehouse],
            ],
            Filiere::Mais, Filiere::Arachide => [
                ['Récolte', 0, 'Champ du producteur, '.$village],
                ['Séchage', 2, 'Aire de séchage, '.$village],
                ['Égrenage et tri', 9, $village],
                ['Stockage en sacs', 11, $warehouse],
            ],
            default => [
                ['Récolte', 0, 'Parcelle du producteur, '.$village],
                ['Tri et calibrage', 1, $village],
                ['Conditionnement', 1, $warehouse],
                ['Transport vers le marché', 2, $region === 'Centre' ? 'Marché du Mfoundi, Yaoundé' : 'Marché Sandaga, Douala'],
            ],
        };

        return array_map(fn (array $step): array => [
            'label' => $step[0],
            'date' => $harvestDate->copy()->addDays($step[1])->toDateString(),
            'location' => $step[2],
        ], $steps);
    }

    /**
     * Create 12 monthly prices (November 2025 → October 2026) per filière and region.
     */
    private function createMarketPrices(): void
    {
        $now = now();
        $rows = [];

        foreach (self::FILIERES as $filiere => $definition) {
            foreach (self::REGION_PRICE_FACTOR as $region => $regionFactor) {
                $isGrownHere = in_array(Filiere::from($filiere), self::COOPERATIVES[$region]['filieres'], true);
                $baseVolume = $isGrownHere ? fake()->numberBetween(400, 2500) : fake()->numberBetween(40, 250);

                foreach (self::SEASONALITY[$filiere] as $month => $seasonFactor) {
                    $rows[] = [
                        'filiere' => $filiere,
                        'region' => $region,
                        'date' => $this->priceMonth($month)->toDateString(),
                        'price_xaf_kg' => $this->roundTo($definition['price'] * $regionFactor * $seasonFactor * fake()->randomFloat(3, 0.97, 1.03), 5),
                        // Harvest months bring more volume and lower prices.
                        'volume_t' => round($baseVolume / $seasonFactor * fake()->randomFloat(2, 0.9, 1.1), 2),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        MarketPrice::query()->insert($rows);
    }

    private function priceMonth(int $offset): Carbon
    {
        return Carbon::parse(self::PRICES_FROM)->addMonthsNoOverflow($offset);
    }

    /**
     * @param  list<string>  $usedNames
     */
    private function uniqueName(string $region, array $usedNames): string
    {
        do {
            $name = fake()->randomElement(self::NAMES[$region]['first']).' '.fake()->randomElement(self::NAMES[$region]['last']);
        } while (in_array($name, $usedNames, true));

        return $name;
    }

    /**
     * Cameroonian mobile number: +237 6XX XX XX XX (MTN 67/68/65x, Orange 69/65x).
     */
    private function phone(): string
    {
        return '+237 6'.fake()->randomElement(['7', '8', '9', '5']).fake()->numerify('# ## ## ##');
    }

    private function roundTo(float|int $value, int $step): int
    {
        return (int) (round($value / $step) * $step);
    }
}
