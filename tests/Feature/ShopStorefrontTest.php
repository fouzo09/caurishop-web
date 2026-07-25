<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndAdminSeeder::class);

    $this->category = Category::create([
        'name' => 'Test', 'slug' => 'test', 'is_active' => true, 'sort_order' => 0,
    ]);

    $this->product = Product::create([
        'type'           => Product::TYPE_SIMPLE,
        'name'           => 'Produit Test',
        'slug'           => 'produit-test',
        'category_id'    => $this->category->id,
        'price'          => 100000,
        'stock_quantity' => 10,
        'is_published'   => true,
        'is_active'      => true,
    ]);
});

it('affiche la page d\'accueil publique', function () {
    $this->get('/')->assertOk()->assertSee('CAURISHOP');
});

it('liste les produits publiés et affiche le détail', function () {
    $this->get(route('shop.products.index'))->assertOk()->assertSee('Produit Test');
    $this->get(route('shop.products.show', $this->product->id))->assertOk()->assertSee('Produit Test');
});

it('permet à un invité d\'ajouter au panier', function () {
    $this->post(route('shop.cart.add'), [
        'product_id' => $this->product->id,
        'quantity'   => 2,
    ])->assertRedirect(route('shop.cart.index'));

    $this->get(route('shop.cart.index'))->assertOk()->assertSee('Produit Test');
});

it('inscrit un nouveau client (User + Customer + rôle customer)', function () {
    $this->post(route('shop.register.store'), [
        'first_name' => 'Aïssatou',
        'last_name'  => 'Diallo',
        'phone'      => '620000001',
        'password'   => 'motdepasse',
    ])->assertRedirect();

    $user = User::where('name', 'Aïssatou Diallo')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('customer'))->toBeTrue();
    expect(Customer::where('user_id', $user->id)->where('type', Customer::TYPE_INDIVIDUAL)->exists())->toBeTrue();
});

it('connexion par téléphone', function () {
    $this->post(route('shop.register.store'), [
        'first_name' => 'Mamadou', 'last_name' => 'Bah', 'phone' => '620000002', 'password' => 'motdepasse',
    ]);
    $this->post(route('shop.logout'));

    $this->post(route('shop.login.store'), [
        'login' => '620000002', 'password' => 'motdepasse',
    ])->assertRedirect();

    $this->assertAuthenticated();
});

it('déroule un checkout et initialise le paiement Djomy (commande en attente)', function () {
    // Simule Djomy pour éviter tout appel réseau.
    $this->app->instance(\App\Services\DjomyService::class, new class extends \App\Services\DjomyService {
        public function __construct() {}
        public function createGatewayPayment(array $payload): array {
            return ['data' => ['transactionId' => 'TXN-TEST', 'redirectUrl' => 'https://pay.djomy.test/redirect']];
        }
    });

    $user = User::create([
        'name' => 'Client Test', 'email' => 'client@test.local',
        'password' => bcrypt('motdepasse'), 'is_active' => true,
    ]);
    $user->assignRole('customer');
    Customer::create([
        'user_id' => $user->id, 'type' => Customer::TYPE_INDIVIDUAL,
        'first_name' => 'Client', 'last_name' => 'Test', 'phone' => '620000003', 'is_active' => true,
    ]);

    $this->actingAs($user);
    $this->post(route('shop.cart.add'), ['product_id' => $this->product->id, 'quantity' => 3]);

    $response = $this->post(route('shop.checkout.store'), [
        'first_name'      => 'Client',
        'last_name'       => 'Test',
        'phone'           => '620000003',
        'address'         => 'Almamya, rue 12',
        'city'            => 'Conakry',
        'delivery_method' => 'standard',
    ]);

    $order = Order::latest('created_at')->first();
    expect($order)->not->toBeNull();
    expect($order->order_type)->toBe(Order::TYPE_CASH);
    expect($order->status)->toBe(Order::STATUS_PENDING_PAYMENT);
    expect((float) $order->total_amount)->toBe(300000.0);

    // Une transaction Djomy est créée et l'utilisateur est redirigé vers le portail.
    expect(\App\Models\DjomyTransaction::where('order_id', $order->id)->exists())->toBeTrue();
    $response->assertRedirect('https://pay.djomy.test/redirect');
});

it('ne casse pas la page de connexion admin existante', function () {
    $this->get('/login')->assertOk();
});
