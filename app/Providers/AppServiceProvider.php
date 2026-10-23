<?php

namespace App\Providers;

use App\Models\Achat\BonLivraisonFournisseur;
use App\Models\Vente\{LivraisonClient, LivraisonDestockage};
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone'));

        Relation::morphMap([
            'BON_LIVRAISON_FOURNISSEUR' => BonLivraisonFournisseur::class,
            'LIVRAISON_CLIENT' => LivraisonClient::class,
            'LIVRAISON_CLIENT_DESTOCKAGE' => LivraisonDestockage::class,
            'BON_LIVRAISON_FOURNISSEUR_DESTOCKAGE' => LivraisonDestockage::class,
        ]);
    }
}
