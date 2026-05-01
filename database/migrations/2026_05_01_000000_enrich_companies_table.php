<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('name', 'raison_sociale');

            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('approved')
                  ->after('id');

            // Gérant / responsable légal
            $table->string('gerant_nom', 100)->nullable()->after('raison_sociale');
            $table->string('gerant_prenom', 100)->nullable()->after('gerant_nom');
            $table->string('gerant_tel', 30)->nullable()->after('gerant_prenom');
            $table->string('gerant_piece', 100)->nullable()->after('gerant_tel');
            $table->string('gerant_adresse')->nullable()->after('gerant_piece');

            // Infos entreprise supplémentaires
            $table->date('date_creation')->nullable()->after('address');
            $table->string('nombre_employes', 50)->nullable()->after('date_creation');

            // Documents juridiques (chemins relatifs storage/public)
            $table->string('doc_rccm')->nullable();
            $table->string('doc_nif')->nullable();
            $table->string('doc_statuts')->nullable();
            $table->string('doc_cni')->nullable();
            $table->string('doc_patente')->nullable();
            $table->string('doc_domicile')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('raison_sociale', 'name');
            $table->dropColumn([
                'status',
                'gerant_nom', 'gerant_prenom', 'gerant_tel', 'gerant_piece', 'gerant_adresse',
                'date_creation', 'nombre_employes',
                'doc_rccm', 'doc_nif', 'doc_statuts', 'doc_cni', 'doc_patente', 'doc_domicile',
            ]);
        });
    }
};
