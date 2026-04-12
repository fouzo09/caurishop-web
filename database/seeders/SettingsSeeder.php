<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Général ──────────────────────────────────────────────
            [
                'key'         => 'app_name',
                'value'       => 'CauriShop',
                'group'       => 'general',
                'type'        => 'text',
                'label'       => 'Nom de l\'application',
                'description' => 'Affiché dans les emails et l\'interface.',
            ],
            [
                'key'         => 'app_currency',
                'value'       => 'GNF',
                'group'       => 'general',
                'type'        => 'text',
                'label'       => 'Devise',
                'description' => 'Devise utilisée pour les montants.',
            ],
            [
                'key'         => 'app_timezone',
                'value'       => 'Africa/Conakry',
                'group'       => 'general',
                'type'        => 'text',
                'label'       => 'Fuseau horaire',
                'description' => null,
            ],
            [
                'key'         => 'app_contact_email',
                'value'       => 'contact@caurishop.gn',
                'group'       => 'general',
                'type'        => 'text',
                'label'       => 'Email de contact',
                'description' => 'Adresse email affichée aux clients.',
            ],
            [
                'key'         => 'app_contact_phone',
                'value'       => '+224 000 000 000',
                'group'       => 'general',
                'type'        => 'text',
                'label'       => 'Téléphone de contact',
                'description' => null,
            ],
            [
                'key'         => 'app_address',
                'value'       => 'Conakry, Guinée',
                'group'       => 'general',
                'type'        => 'textarea',
                'label'       => 'Adresse',
                'description' => null,
            ],

            // ── Crédit ───────────────────────────────────────────────
            [
                'key'         => 'credit_default_interest_rate',
                'value'       => '0',
                'group'       => 'credit',
                'type'        => 'number',
                'label'       => 'Taux d\'intérêt par défaut (%)',
                'description' => 'Taux appliqué si la company n\'a pas de taux propre.',
            ],
            [
                'key'         => 'credit_max_months',
                'value'       => '24',
                'group'       => 'credit',
                'type'        => 'number',
                'label'       => 'Durée maximale du crédit (mois)',
                'description' => null,
            ],
            [
                'key'         => 'credit_min_down_payment_percent',
                'value'       => '10',
                'group'       => 'credit',
                'type'        => 'number',
                'label'       => 'Acompte minimum (%)',
                'description' => 'Pourcentage minimum de l\'acompte sur le total.',
            ],
            [
                'key'         => 'credit_global_limit',
                'value'       => '50000000',
                'group'       => 'credit',
                'type'        => 'number',
                'label'       => 'Plafond de crédit global (GNF)',
                'description' => 'Montant maximal total en crédit en cours.',
            ],
            [
                'key'         => 'credit_late_penalty_rate',
                'value'       => '2',
                'group'       => 'credit',
                'type'        => 'number',
                'label'       => 'Pénalité de retard (%)',
                'description' => 'Pourcentage appliqué sur l\'échéance en retard.',
            ],

            // ── Stock ─────────────────────────────────────────────────
            [
                'key'         => 'stock_low_threshold',
                'value'       => '5',
                'group'       => 'stock',
                'type'        => 'number',
                'label'       => 'Seuil de stock bas (unités)',
                'description' => 'Une alerte est émise quand le stock passe sous ce seuil.',
            ],
            [
                'key'         => 'stock_alert_enabled',
                'value'       => '1',
                'group'       => 'stock',
                'type'        => 'boolean',
                'label'       => 'Activer les alertes de stock bas',
                'description' => null,
            ],

            // ── Notifications ─────────────────────────────────────────
            [
                'key'         => 'notif_new_order',
                'value'       => '1',
                'group'       => 'notifications',
                'type'        => 'boolean',
                'label'       => 'Nouvelle commande',
                'description' => 'Notifier quand une nouvelle commande est créée.',
            ],
            [
                'key'         => 'notif_payment_received',
                'value'       => '1',
                'group'       => 'notifications',
                'type'        => 'boolean',
                'label'       => 'Paiement reçu',
                'description' => 'Notifier quand un paiement est enregistré.',
            ],
            [
                'key'         => 'notif_installment_late',
                'value'       => '1',
                'group'       => 'notifications',
                'type'        => 'boolean',
                'label'       => 'Échéance en retard',
                'description' => 'Notifier quand une échéance passe en statut "late".',
            ],
            [
                'key'         => 'notif_days_before_due',
                'value'       => '3',
                'group'       => 'notifications',
                'type'        => 'number',
                'label'       => 'Jours avant échéance pour rappel',
                'description' => 'Envoyer un rappel X jours avant la date d\'échéance.',
            ],
        ];

        foreach ($settings as $data) {
            Setting::firstOrCreate(['key' => $data['key']], $data);
        }
    }
}
