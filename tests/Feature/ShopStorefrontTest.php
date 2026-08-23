<?php

use App\Models\Category;
use App\Models\City;
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
        'city_id'         => City::where('slug', 'conakry')->value('id'),
        'quartier'        => 'Almamya',
        'precision'       => 'rue 12, en face de la pharmacie',
        'delivery_method' => 'standard',
    ]);

    $order = Order::latest('created_at')->first();
    expect($order)->not->toBeNull();
    expect($order->order_type)->toBe(Order::TYPE_CASH);
    expect($order->status)->toBe(Order::STATUS_PENDING_PAYMENT);
    expect((float) $order->total_amount)->toBe(300000.0);
    expect($order->shipping_city)->toBe('Conakry');
    expect($order->shipping_quartier)->toBe('Almamya');
    expect($order->shipping_precision)->toBe('rue 12, en face de la pharmacie');

    // Une transaction Djomy est créée et l'utilisateur est redirigé vers le portail.
    expect(\App\Models\DjomyTransaction::where('order_id', $order->id)->exists())->toBeTrue();
    $response->assertRedirect('https://pay.djomy.test/redirect');
});

it('renvoie l\'ancienne URL /login vers la connexion unique', function () {
    // Depuis la fusion des espaces, /login redirige vers /connexion.
    $this->get('/login')->assertRedirect(route('shop.login'));
    $this->get(route('shop.login'))->assertOk();
});

/*
|--------------------------------------------------------------------------
| Adresse : ville (référentiel) + quartier + précision
|--------------------------------------------------------------------------
*/

it('seede les villes de livraison', function () {
    expect(City::pluck('name')->all())->toContain('Conakry', 'Kindia', 'Boffa', 'Boké');
});

it('enregistre une adresse en ville + quartier + précision', function () {
    $user = client('adresse@test.local', '620000010');
    $this->actingAs($user);

    $this->post(route('shop.account.addresses.store'), [
        'label'     => 'Domicile',
        'full_name' => 'Client Test',
        'phone'     => '620000010',
        'city_id'   => City::where('slug', 'kindia')->value('id'),
        'quartier'  => 'Almamya',
        'precision' => 'rue KA 020',
    ])->assertRedirect(route('shop.account.addresses'));

    $address = $user->customer->addresses()->first();
    expect($address->cityName())->toBe('Kindia');
    expect($address->quartier)->toBe('Almamya');
    expect($address->precision)->toBe('rue KA 020');
    expect($address->is_default)->toBeTrue();   // première adresse du carnet
    expect($address->inline())->toBe('Almamya — rue KA 020, Kindia');
});

it('affiche le carnet d\'adresses et le checkout avec les trois champs', function () {
    $user = client('adresse3@test.local', '620000012');
    $this->actingAs($user);

    $this->get(route('shop.account.addresses'))
        ->assertOk()
        ->assertSee('name="city_id"', false)
        ->assertSee('name="quartier"', false)
        ->assertSee('name="precision"', false)
        ->assertSee('Conakry');

    $this->post(route('shop.cart.add'), ['product_id' => $this->product->id, 'quantity' => 1]);

    $this->get(route('shop.checkout.index'))
        ->assertOk()
        ->assertSee('name="city_id"', false)
        ->assertSee('name="quartier"', false)
        ->assertSee('name="precision"', false);
});

it('refuse une adresse sans ville du référentiel', function () {
    $this->actingAs(client('adresse2@test.local', '620000011'));

    $this->post(route('shop.account.addresses.store'), [
        'full_name' => 'Client Test',
        'phone'     => '620000011',
        'city_id'   => 9999,
        'quartier'  => 'Almamya',
    ])->assertSessionHasErrors('city_id');
});

/*
|--------------------------------------------------------------------------
| Avis et commentaires produits
|--------------------------------------------------------------------------
*/

it('permet à un client connecté de laisser un avis', function () {
    $user = client('avis@test.local', '620000020');
    $this->actingAs($user);

    $this->post(route('shop.products.reviews.store', $this->product->id), [
        'rating'  => 5,
        'title'   => 'Excellent',
        'comment' => 'Produit conforme, livraison rapide.',
    ])->assertRedirect();

    $this->get(route('shop.products.show', $this->product->id))
        ->assertOk()
        ->assertSee('Produit conforme, livraison rapide.');

    $product = Product::withRatings()->find($this->product->id);
    expect($product->ratingAverage())->toBe(5.0);
    expect($product->ratingCount())->toBe(1);
});

it('met à jour l\'avis existant plutôt que d\'en créer un second', function () {
    $user = client('avis2@test.local', '620000021');
    $this->actingAs($user);

    $this->post(route('shop.products.reviews.store', $this->product->id), ['rating' => 2, 'comment' => 'Bof, sans plus.']);
    $this->post(route('shop.products.reviews.store', $this->product->id), ['rating' => 4, 'comment' => 'Finalement très bien.']);

    expect(\App\Models\ProductReview::where('product_id', $this->product->id)->count())->toBe(1);
    expect(\App\Models\ProductReview::first()->rating)->toBe(4);
});

it('valide la note et le commentaire', function () {
    $this->actingAs(client('avis3@test.local', '620000022'));

    $this->post(route('shop.products.reviews.store', $this->product->id), ['rating' => 9, 'comment' => 'Top'])
        ->assertSessionHasErrors(['rating', 'comment']);
});

it('refuse un avis à un visiteur non connecté', function () {
    $this->post(route('shop.products.reviews.store', $this->product->id), [
        'rating' => 5, 'comment' => 'Anonyme mais enthousiaste.',
    ])->assertRedirect(route('shop.login'));

    expect(\App\Models\ProductReview::count())->toBe(0);
});

it('ne laisse supprimer que son propre avis', function () {
    $author = client('avis4@test.local', '620000023');
    $this->actingAs($author);
    $this->post(route('shop.products.reviews.store', $this->product->id), ['rating' => 5, 'comment' => 'Mon avis à moi.']);
    $review = \App\Models\ProductReview::first();

    $this->actingAs(client('avis5@test.local', '620000024'));
    $this->delete(route('shop.products.reviews.destroy', $review->id))->assertForbidden();

    $this->actingAs($author);
    $this->delete(route('shop.products.reviews.destroy', $review->id))->assertRedirect();
    expect(\App\Models\ProductReview::count())->toBe(0);
});

/** Crée un utilisateur client (User + Customer) prêt à commander ou commenter. */
function client(string $email, string $phone): User
{
    $user = User::create([
        'name' => 'Client Test', 'email' => $email,
        'password' => bcrypt('motdepasse'), 'is_active' => true,
    ]);
    $user->assignRole('customer');

    Customer::create([
        'user_id' => $user->id, 'type' => Customer::TYPE_INDIVIDUAL,
        'first_name' => 'Client', 'last_name' => 'Test', 'phone' => $phone, 'is_active' => true,
    ]);

    return $user->fresh();
}

/*
|--------------------------------------------------------------------------
| Modération des avis côté admin
|--------------------------------------------------------------------------
*/

/** Avis déposé par un client, prêt à être modéré. */
function reviewOn(Product $product, string $comment = 'Un commentaire à modérer.'): \App\Models\ProductReview
{
    $customer = client('moderation' . uniqid() . '@test.local', '62000' . random_int(1000, 9999))->customer;

    return \App\Models\ProductReview::create([
        'product_id'  => $product->id,
        'customer_id' => $customer->id,
        'rating'      => 3,
        'title'       => 'Correct',
        'comment'     => $comment,
        'is_approved' => true,
    ]);
}

/** Admin seedé par RolesAndAdminSeeder. */
function admin(): User
{
    return User::where('email', 'admin@caurishop.test')->firstOrFail();
}

it('liste les avis d\'un produit côté admin', function () {
    $this->seed(\Database\Seeders\PermissionsSeeder::class);
    reviewOn($this->product, 'Livraison impeccable.');
    reviewOn($this->product, 'Produit décevant.');

    $this->actingAs(admin())
        ->get(route('admin.products.reviews.index', $this->product))
        ->assertOk()
        ->assertSee('Livraison impeccable.')
        ->assertSee('Produit décevant.');
});

it('filtre les avis par statut et par note', function () {
    $this->seed(\Database\Seeders\PermissionsSeeder::class);
    $visible = reviewOn($this->product, 'Avis visible.');
    $masque  = reviewOn($this->product, 'Avis masqué.');
    $masque->update(['is_approved' => false]);

    $this->actingAs(admin())
        ->get(route('admin.products.reviews.index', [$this->product, 'status' => 'hidden']))
        ->assertOk()
        ->assertSee('Avis masqué.')
        ->assertDontSee('Avis visible.');

    $this->actingAs(admin())
        ->get(route('admin.products.reviews.index', [$this->product, 'rating' => 1]))
        ->assertOk()
        ->assertDontSee($visible->comment);
});

it('masque un avis sans le supprimer, puis le republie', function () {
    $this->seed(\Database\Seeders\PermissionsSeeder::class);
    $review = reviewOn($this->product);

    $this->actingAs(admin())->post(route('admin.products.reviews.toggle', [$this->product, $review]))->assertRedirect();
    expect($review->fresh()->is_approved)->toBeFalse();

    // Masqué : plus compté dans la note publique ni visible sur la fiche.
    $product = Product::withRatings()->find($this->product->id);
    expect($product->ratingCount())->toBe(0);
    $this->get(route('shop.products.show', $this->product->id))->assertOk()->assertDontSee($review->comment);

    $this->actingAs(admin())->post(route('admin.products.reviews.toggle', [$this->product, $review]))->assertRedirect();
    expect($review->fresh()->is_approved)->toBeTrue();
});

it('supprime un avis depuis l\'admin', function () {
    $this->seed(\Database\Seeders\PermissionsSeeder::class);
    $review = reviewOn($this->product);

    $this->actingAs(admin())
        ->delete(route('admin.products.reviews.destroy', [$this->product, $review]))
        ->assertRedirect();

    expect(\App\Models\ProductReview::find($review->id))->toBeNull();
});

it('refuse de modérer un avis qui n\'appartient pas au produit de l\'URL', function () {
    $this->seed(\Database\Seeders\PermissionsSeeder::class);
    $other = Product::create([
        'type' => Product::TYPE_SIMPLE, 'name' => 'Autre produit', 'slug' => 'autre-produit',
        'price' => 50000, 'stock_quantity' => 5, 'is_published' => true, 'is_active' => true,
    ]);
    $review = reviewOn($this->product);

    $this->actingAs(admin())
        ->delete(route('admin.products.reviews.destroy', [$other, $review]))
        ->assertNotFound();

    expect(\App\Models\ProductReview::find($review->id))->not->toBeNull();
});

it('ferme la modération des avis aux non-admins', function () {
    $this->seed(\Database\Seeders\PermissionsSeeder::class);
    $review = reviewOn($this->product);

    $this->actingAs(client('intrus@test.local', '620000099'))
        ->get(route('admin.products.reviews.index', $this->product))
        ->assertForbidden();

    $this->actingAs(client('intrus2@test.local', '620000098'))
        ->delete(route('admin.products.reviews.destroy', [$this->product, $review]))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Stockage des fichiers (DigitalOcean Spaces en production)
|--------------------------------------------------------------------------
*/

it('range les images produit sous images/products/{id}', function () {
    \Illuminate\Support\Facades\Storage::fake(\App\Support\Media::diskName());
    $this->seed(\Database\Seeders\PermissionsSeeder::class);

    $this->actingAs(admin())->post(
        route('admin.products.images.store', $this->product),
        ['images' => [\Illuminate\Http\UploadedFile::fake()->image('visuel.jpg')]],
    )->assertRedirect();

    $image = $this->product->images()->first();
    expect($image->path)->toStartWith('images/products/' . $this->product->id . '/');
    \Illuminate\Support\Facades\Storage::disk(\App\Support\Media::diskName())->assertExists($image->path);
});

it('range chaque document d\'entreprise dans son dossier racine', function () {
    \Illuminate\Support\Facades\Storage::fake(\App\Support\Media::diskName());

    $company = \App\Models\Company::create([
        'raison_sociale' => 'Test SARL', 'email' => 'contact@test-sarl.gn',
        'phone' => '620000200', 'status' => \App\Models\Company::STATUS_PENDING, 'is_active' => false,
    ]);

    $folder = \App\Support\Media::companyDoc('doc_rccm', $company->id);
    expect($folder)->toBe('rccm/' . $company->id);
    expect(\App\Support\Media::companyDoc('doc_nif', $company->id))->toBe('nif/' . $company->id);
    expect(\App\Support\Media::companyDoc('inconnu', $company->id))->toBeNull();

    $path = \Illuminate\Http\UploadedFile::fake()->create('rccm.pdf', 12, 'application/pdf')
        ->store($folder, \App\Support\Media::diskName());

    expect($path)->toStartWith('rccm/' . $company->id . '/');
    \Illuminate\Support\Facades\Storage::disk(\App\Support\Media::diskName())->assertExists($path);
});

it('construit les URL publiques depuis le disque média', function () {
    config(['filesystems.default' => 'spaces']);

    expect(\App\Support\Media::url('images/products/1/img_0.jpg'))
        ->toBe('https://caurishop.sfo3.digitaloceanspaces.com/images/products/1/img_0.jpg');
    expect(\App\Support\Media::url(null))->toBeNull();
});
