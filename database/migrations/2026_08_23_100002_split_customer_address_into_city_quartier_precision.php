<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * L'adresse de livraison passe de « city (texte) + address (texte libre) »
     * à « ville (référentiel) + quartier + précision ».
     */
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('phone')->constrained('cities')->restrictOnDelete();
            $table->string('quartier', 120)->nullable()->after('city_id');
            $table->string('precision', 255)->nullable()->after('quartier');
        });

        // Reprise de l'existant : la ville saisie librement est rattachée au
        // référentiel (créée si elle n'y figure pas), l'adresse devient le quartier.
        foreach (DB::table('customer_addresses')->get() as $row) {
            DB::table('customer_addresses')->where('id', $row->id)->update([
                'city_id'  => $this->cityId($row->city),
                'quartier' => Str::limit((string) $row->address, 119, ''),
            ]);
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn(['city', 'address']);
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('city');
        });

        foreach (DB::table('customer_addresses')->get() as $row) {
            DB::table('customer_addresses')->where('id', $row->id)->update([
                'city'    => $row->city_id ? DB::table('cities')->where('id', $row->city_id)->value('name') : null,
                'address' => trim($row->quartier . ($row->precision ? ' — ' . $row->precision : '')),
            ]);
        }

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn(['quartier', 'precision']);
        });
    }

    /** Id de la ville portant ce nom, créée à la volée si elle manque au référentiel. */
    private function cityId(?string $name): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);
        $id   = DB::table('cities')->where('slug', $slug)->value('id');

        return $id ?: DB::table('cities')->insertGetId([
            'name'       => $name,
            'slug'       => $slug,
            'sort_order' => 99,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
