<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Securite\{ProfilController, UserController, RoleController};
use App\Http\Controllers\Portail\PortailController;
use App\Http\Controllers\Parametre\SocieteController;
use App\Http\Controllers\Parametre\PointVenteController;
use App\Http\Controllers\Parametre\DepotController;
use App\Http\Controllers\Parametre\CaisseController;
use App\Http\Controllers\Parametre\UniteMesureController;
use App\Http\Controllers\Parametre\ConversionUniteController;

use App\Http\Controllers\Catalogues\{FamilleArticleController, ArticleController, TarificationController};
use App\Http\Controllers\Achat\{FournisseurApprovisionnementController, FournisseurController, ProgrammationAchatController, LigneProgrammationAchatController};
use App\Http\Controllers\Achat\{BonCommandeController, LigneBonCommandeController, FactureFournisseurController, LigneFactureFournisseurController, ReglementFournisseurController,  BonLivraisonFournisseurController, LigneBonLivraisonFournisseurController};
use App\Http\Controllers\Vente\{AcompteClientController, ClientController, SessionCaisseController, FactureClientController, ReglementClientController, LivraisonClientController, LivraisonPvClientController, LigneLivraisonClientController, ProformaController, RecouvrementController, RequeteController, TransportController};
use App\Http\Controllers\Parametre\ChauffeurController;
use App\Http\Controllers\Parametre\VehiculeController;
use App\Http\Controllers\Revendeur\FactureRevendeurController;

use App\Http\Controllers\Rapport\{RapportVenteController, SoldeInitialClientController, SoldeInitialFournisseurController, RapportAchatController, RapportStockController, RapportValorisationController, StockRotationController, StockAlertController, RapportCreanceController};
use App\Http\Controllers\Revendeur\SpecialController;
use App\Models\Achat\BonCommande;
use App\Models\Achat\BonLivraisonFournisseur;
use App\Models\Achat\FactureFournisseur;
use App\Models\Achat\FournisseurApprovisionnement;
use App\Models\Achat\LigneBonCommande;
use App\Models\Achat\LigneBonLivraisonFournisseur;
use App\Models\Achat\LigneProgrammationAchat;
use App\Models\Achat\ProgrammationAchat;
use App\Models\Achat\ReglementFournisseur;
use App\Models\Catalogue\Article;
use App\Models\Catalogue\Inventaire;
use App\Models\Parametre\Caisse;
use App\Models\Parametre\ConversionUnite;
use App\Models\Stock\StockDepot;
use App\Models\Stock\StockMouvement;
use App\Models\Tarification;
use App\Models\Vente\Devis;
use App\Models\Vente\FactureClient;
use App\Models\Vente\LigneLivraisonClient;
use App\Models\Vente\LivraisonClient;
use App\Models\Vente\Recouvrement;
use App\Models\Vente\ReglementClient;
use App\Models\Vente\Requete;
use App\Models\Vente\SessionCaisse;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// DEBUGGING ROUTES
Route::get("/debug", function () {
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    Recouvrement::query()->delete();
    FactureClient::query()->delete();
    Devis::query()->delete();

    Inventaire::query()->delete();

    FournisseurApprovisionnement::query()->delete();

    LigneLivraisonClient::query()->delete();
    LivraisonClient::query()->delete();

    ReglementFournisseur::query()->delete();
    ReglementClient::query()->delete();

    StockDepot::query()->delete();
    StockMouvement::query()->delete();

    ProgrammationAchat::query()->delete();
    LigneProgrammationAchat::query()->delete();

    BonCommande::query()->delete();
    BonCommande::query()->delete();
    LigneBonCommande::query()->delete();

    FactureFournisseur::query()->delete();
    BonLivraisonFournisseur::query()->update(["deleted_at" => now()]);
    LigneBonLivraisonFournisseur::query()->update(["deleted_at" => now()]);

    Article::query()->delete();

    ReglementFournisseur::query()->update(["deleted_at" => now()]);


    Article::query()->delete();

    Requete::query()->delete();

    Tarification::query()->delete();
    ConversionUnite::query()->delete();

    SessionCaisse::query()->delete();

    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    return "Opération éffectuée avec succès!!";
});

// Routes publiques
Route::middleware('guest')->group(function () {
    Route::get('/', [UserController::class, 'showLogin'])->name('login-portail');
    Route::post('/login', [UserController::class, 'login'])->name('login');
});

// Routes protégées
Route::middleware('auth')->group(function () {

    Route::get('acompte-clients/get-clients', [AcompteClientController::class, 'getClients'])
        ->name('acompte-clients.get-clients');

    Route::put('/vente/acomptes/{acompte}', [AcompteClientController::class, 'update'])->name('acomptes.update');
    Route::prefix('vente/acomptes')->group(function () {
        Route::post('{acompte}/validate', [AcompteClientController::class, 'validate_acompte'])
            ->name('acomptes.validate');
        Route::post('{acompte}/reject', [AcompteClientController::class, 'reject'])
            ->name('acomptes.reject');
    });
    Route::resource('roles', RoleController::class);
    // Gestion des utilisateurs et rôles (nouveaux ajouts)

    Route::prefix('users')->group(function () {
        Route::get('/check-email', [UserController::class, 'checkEmailAvailability']);  // Doit être avant les routes avec paramètres
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');  // Ajout de la route show
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::resource('/profil', ProfilController::class)->only(['index']);
    Route::put('/profil/update-password', [ProfilController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->name('roles.permissions.update');
    });

    // Route de déconnexion
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    // Route d'accueil (protégée)
    Route::get('/portail', [PortailController::class, 'index'])->name('portail');

    // Routes des paramètres
    Route::prefix('parametres')->group(function () {
        // Routes société
        Route::prefix('societe')->group(function () {
            Route::get('/', [SocieteController::class, 'index'])->name('configuration.index');
            Route::post('/update', [SocieteController::class, 'update'])->name('configuration.update');
            Route::post('/logo', [SocieteController::class, 'updateLogo'])->name('configuration.updateLogo');
            Route::delete('/logo', [SocieteController::class, 'deleteLogo'])->name('configuration.deleteLogo');
            Route::post('/reset', [SocieteController::class, 'reset'])->name('configuration.reset');
            Route::post('/update-logo', [SocieteController::class, 'updateLogo'])->name('configuration.update-logo');
        });

        Route::prefix('points-vente')->group(function () {
            Route::get('/', [PointVenteController::class, 'index'])->name('point-vente.index');
            Route::post('/', [PointVenteController::class, 'store'])->name('point-vente.store');
            Route::get('/{pointVente}/edit', [PointVenteController::class, 'edit'])->name('point-vente.edit');
            Route::put('/{pointVente}', [PointVenteController::class, 'update'])->name('point-vente.update');
            Route::delete('/{pointVente}', [PointVenteController::class, 'destroy'])->name('point-vente.destroy');
            Route::post('/{pointVente}/toggle-status', [PointVenteController::class, 'toggleStatus'])->name('point-vente.toggle-status');
            Route::post('/check-point-vente-code', [PointVenteController::class, 'uniqueCode'])->name('check.point_vente.code');
        });

        Route::prefix('depots')->group(function () {
            Route::get('/', [DepotController::class, 'index'])->name('depot.index');
            Route::post('/', [DepotController::class, 'store'])->name('depot.store');
            Route::get('/{depot}/edit', [DepotController::class, 'edit'])->name('depot.edit');
            Route::put('/{depot}', [DepotController::class, 'update'])->name('depot.update');
            Route::delete('/{depot}', [DepotController::class, 'destroy'])->name('depot.destroy');
            Route::post('/{depot}/toggle-status', [DepotController::class, 'toggleStatus'])->name('depot.toggle-status');
            Route::post('/check-depot-code', [DepotController::class, 'uniqueCode'])->name('check.depot.code');
        });

        Route::prefix('caisses')->group(function () {
            Route::get('/', [CaisseController::class, 'index'])->name('caisse.index');
            Route::post('/', [CaisseController::class, 'store'])->name('caisse.store');
            Route::get('/{caisse}/edit', [CaisseController::class, 'edit'])->name('caisse.edit');
            Route::put('/{caisse}', [CaisseController::class, 'update'])->name('caisse.update');
            Route::delete('/{caisse}', [CaisseController::class, 'destroy'])->name('caisse.destroy');
            Route::post('/{caisse}/toggle-status', [CaisseController::class, 'toggleStatus'])->name('caisse.toggle-status');
            Route::post('/check-caisse-code', [CaisseController::class, 'uniqueCode'])->name('check.caisse.code');
        });

        Route::prefix('unites-mesure')->group(function () {
            Route::get('/', [UniteMesureController::class, 'index'])->name('unite-mesure.index');
            Route::get('/list', [UniteMesureController::class, 'list'])->name('unite-mesure.list');
            Route::post('/', [UniteMesureController::class, 'store'])->name('unite-mesure.store');
            Route::get('/{uniteMesure}/edit', [UniteMesureController::class, 'edit'])->name('unite-mesure.edit');
            Route::put('/{uniteMesure}', [UniteMesureController::class, 'update'])->name('unite-mesure.update');
            Route::delete('/{uniteMesure}', [UniteMesureController::class, 'destroy'])->name('unite-mesure.destroy');
            Route::post('/{uniteMesure}/toggle-status', [UniteMesureController::class, 'toggleStatus'])->name('unite-mesure.toggle-status');
            Route::post('/check-unite-code', [UniteMesureController::class, 'uniqueCode'])->name('check.unite.code');
        });

        Route::prefix('conversions')->group(function () {
            Route::get('/', [ConversionUniteController::class, 'index'])->name('conversion.index');
            Route::post('/', [ConversionUniteController::class, 'store'])->name('conversion.store');
            Route::get('/{conversion}/edit', [ConversionUniteController::class, 'edit'])->name('conversion.edit');
            Route::put('/{conversion}', [ConversionUniteController::class, 'update'])->name('conversion.update');
            Route::delete('/{conversion}', [ConversionUniteController::class, 'destroy'])->name('conversion.destroy');
            Route::post('/{conversion}/toggle-status', [ConversionUniteController::class, 'toggleStatus'])->name('conversion.toggle-status');
            Route::get('/by-unite/{unite}', [ConversionUniteController::class, 'getByUnite'])->name('conversion.by-unite');
            Route::get('/by-famille/{famille}', [ConversionUniteController::class, 'getByFamille'])->name('conversion.by-famille');
            Route::post('/check-existence', [ConversionUniteController::class, 'checkExistence'])->name('conversion.check-existence');
            Route::get('/template/download', [FamilleArticleController::class, 'downloadTemplate'])
                ->name('catalogue.famille-article.template.download');

            Route::post('/import', [FamilleArticleController::class, 'import'])
                ->name('catalogue.famille-article.import');
        });

        Route::prefix('chauffeurs')->group(function () {
            Route::get('/', [ChauffeurController::class, 'index'])->name('chauffeur.index');
            Route::post('/', [ChauffeurController::class, 'store'])->name('chauffeur.store');
            Route::get('/{chauffeur}/edit', [ChauffeurController::class, 'edit'])->name('chauffeur.edit');
            Route::put('/{chauffeur}', [ChauffeurController::class, 'update'])->name('chauffeur.update');
            Route::delete('/{chauffeur}', [ChauffeurController::class, 'destroy'])->name('chauffeur.destroy');
            Route::get('/template/download', [ChauffeurController::class, 'downloadTemplate'])->name('chauffeur.template');
            Route::post('/import', [ChauffeurController::class, 'import'])->name('chauffeur.import');
        });

        Route::prefix('vehicules')->group(function () {
            Route::get('/', [VehiculeController::class, 'index'])->name('vehicule.index');
            Route::post('/', [VehiculeController::class, 'store'])->name('vehicule.store');
            Route::get('/{vehicule}/edit', [VehiculeController::class, 'edit'])->name('vehicule.edit');
            Route::put('/{vehicule}', [VehiculeController::class, 'update'])->name('vehicule.update');
            Route::delete('/{vehicule}', [VehiculeController::class, 'destroy'])->name('vehicule.destroy');
            Route::get('/template/download', [VehiculeController::class, 'downloadTemplate'])->name('vehicule.template');
            Route::post('/import', [VehiculeController::class, 'import'])->name('vehicule.import');
        });
    });

    Route::prefix('catalogue')->middleware(['auth'])->group(function () {

        Route::prefix('famille-articles')->group(function () {
            Route::get('/', [FamilleArticleController::class, 'index'])->name('famille-article.index');
            Route::post('/', [FamilleArticleController::class, 'store'])->name('famille-article.store');
            Route::get('/{familleArticle}/edit', [FamilleArticleController::class, 'edit'])->name('famille-article.edit');
            Route::put('/{familleArticle}', [FamilleArticleController::class, 'update'])->name('famille-article.update');
            Route::delete('/{familleArticle}', [FamilleArticleController::class, 'destroy'])->name('famille-article.destroy');
            Route::post('/{familleArticle}/toggle-status', [FamilleArticleController::class, 'toggleStatus'])->name('famille-article.toggle-status');
            Route::post('/check-famille-code', [FamilleArticleController::class, 'uniqueCode'])->name('check.famille.code');
        });

        Route::prefix('tarifications')->group(function () {
            Route::get('/', [TarificationController::class, 'index'])->name('tarification.index');
            Route::post('/', [TarificationController::class, 'store'])->name('tarification.store');
            Route::get('/{tarification}/edit', [TarificationController::class, 'edit'])->name('tarification.edit');
            Route::put('/{tarification}', [TarificationController::class, 'update'])->name('tarification.update');
            Route::delete('/{tarification}', [TarificationController::class, 'destroy'])->name('tarification.destroy');
            Route::post('/{tarification}/toggle-status', [TarificationController::class, 'toggleStatus'])->name('tarification.toggle-status');
            Route::post('{article}/update-all', [TarificationController::class, 'updateAll'])->name('tarification.update-all');
            // Routes supplémentaires
            Route::get('/by-article/{article}', [TarificationController::class, 'getByArticle'])->name('tarification.by-article');
        });

        Route::prefix('articles')->group(function () {
            // Routes principales CRUD
            Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
            Route::post('/', [ArticleController::class, 'store'])->name('articles.store');
            Route::get('/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
            Route::get('/{article}/show', [ArticleController::class, 'show'])->name('articles.show');
            Route::post('/{article}/article-affect', [ArticleController::class, 'articleAffect'])->name('articles.affect');
            Route::put('/{article}', [ArticleController::class, 'update'])->name('articles.update');
            Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

            // inventaires
            Route::patch('/inventaires/store', [ArticleController::class, 'storeMultipleInventaires'])->name('articles.storeMultipleInventaires');

            // Routes additionnelles pour les fonctionnalités spécifiques
            Route::get('/search', [ArticleController::class, 'search'])->name('articles.search');
            Route::get('/filter', [ArticleController::class, 'filter'])->name('articles.filter');
            Route::post('/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('articles.toggle-status');
            Route::post('/{article}/update-stock', [ArticleController::class, 'updateStock'])->name('articles.update-stock');
            Route::get('/list', [ArticleController::class, 'index'])->name('articles.list');

            Route::get('/articles/generate-code', [ArticleController::class, 'generateUniqueCode'])
                ->name('articles.generate-code');

            Route::get('/filter', [ArticleController::class, 'filter'])->name('articles.filter');

            Route::get('/template/download', [ArticleController::class, 'downloadTemplate'])
                ->name('catalogue.article.template.download');

            Route::post('/import', [ArticleController::class, 'import'])
                ->name('catalogue.article.import');
        });
    });

    Route::prefix('achat')->middleware(['auth'])->group(function () {
        Route::prefix('fournisseurs')->group(function () {
            Route::get('/', [FournisseurController::class, 'index'])->name('fournisseur.index');
            Route::post('/', [FournisseurController::class, 'store'])->name('fournisseur.store');
            Route::get('/{fournisseur}/edit', [FournisseurController::class, 'edit'])->name('fournisseur.edit');
            Route::put('/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseur.update');
            Route::delete('/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseur.destroy');
            Route::get('/template/download', [FournisseurController::class, 'downloadTemplate'])->name('fournisseur.template');
            Route::post('/import', [FournisseurController::class, 'import'])->name('fournisseur.import');
        });

        Route::prefix('programmations')->group(function () {
            // Routes principales de programmation
            Route::get('/', [ProgrammationAchatController::class, 'index'])->name('programmations.index');
            Route::post('/', [ProgrammationAchatController::class, 'store'])->name('programmations.store');
            Route::get('/{programmation}/edit', [ProgrammationAchatController::class, 'edit'])->name('programmations.edit');
            Route::put('/{programmation}', [ProgrammationAchatController::class, 'update'])->name('programmations.update');
            Route::delete('/{programmation}', [ProgrammationAchatController::class, 'destroy'])->name('programmations.destroy');
            Route::get('/{programmation}/show', [ProgrammationAchatController::class, 'show'])->name('programmations.show');
            Route::put('/{programmation}/rejet', [ProgrammationAchatController::class, 'rejectProgrammation'])->name('programmations.rejet');
            Route::get('/validees', [ProgrammationAchatController::class, 'validees'])->name('programmations.validees');

            // Gestion des articles de la programmation
            Route::get('/{programmation}/articles', [ProgrammationAchatController::class, 'articles'])->name('programmations.articles');
            Route::post('/{programmation}/articles', [ProgrammationAchatController::class, 'addArticles'])->name('programmations.articles.add');
            Route::delete('/{programmation}/articles/{article}', [ProgrammationAchatController::class, 'removeArticle'])->name('programmations.articles.remove');

            // Validation et workflow
            Route::post('/{programmation}/validate', [ProgrammationAchatController::class, 'validated'])->name('programmations.validate');
            Route::post('/{programmation}/cancel', [ProgrammationAchatController::class, 'cancel'])->name('programmations.cancel');

            // Génération de code
            Route::get('/generate-code', [ProgrammationAchatController::class, 'generateCode'])->name('programmations.generate-code');

            // Import/Export
            Route::get('/template/download', [ProgrammationAchatController::class, 'downloadTemplate'])->name('programmations.template');
            Route::post('/import', [ProgrammationAchatController::class, 'import'])->name('programmations.import');
            Route::get('/{programmation}/export', [ProgrammationAchatController::class, 'export'])->name('programmations.export');

            // Recherche et filtres
            Route::get('/search', [ProgrammationAchatController::class, 'search'])->name('programmations.search');
            Route::get('/filter', [ProgrammationAchatController::class, 'filter'])->name('programmations.filter');

            // API pour les composants dynamiques
            Route::get('/fournisseurs/{fournisseur}/articles', [ProgrammationAchatController::class, 'getArticlesByFournisseur'])
                ->name('programmations.fournisseur.articles');
            Route::get('/articles/{article}/unites', [ProgrammationAchatController::class, 'getUnitesByArticle'])
                ->name('programmations.article.unites');
        });

        // Routes pour les lignes de programmation
        Route::prefix('ligne-programmations')->group(function () {
            Route::post('/', [LigneProgrammationAchatController::class, 'store'])->name('ligne-programmations.store');
            Route::put('/{ligne}', [LigneProgrammationAchatController::class, 'update'])->name('ligne-programmations.update');
            Route::delete('/{ligne}', [LigneProgrammationAchatController::class, 'destroy'])->name('ligne-programmations.destroy');
            Route::post('/bulk', [LigneProgrammationAchatController::class, 'bulkStore'])->name('ligne-programmations.bulk.store');
            Route::put('/bulk', [LigneProgrammationAchatController::class, 'bulkUpdate'])->name('ligne-programmations.bulk.update');
        });

        // Routes pour les bons de commande
        Route::prefix('bon-commandes')->group(function () {
            // Routes principales des bons de commande
            Route::get('/', [BonCommandeController::class, 'index'])->name('bon-commandes.index');
            Route::post('/', [BonCommandeController::class, 'store'])->name('bon-commandes.store');
            Route::get('/{bonCommande}', [BonCommandeController::class, 'show'])->name('bon-commandes.show');
            Route::put('/{bonCommande}', [BonCommandeController::class, 'update'])->name('bon-commandes.update');
            Route::post('/{bonCommande}/validate', [BonCommandeController::class, 'validated'])->name('bon-commandes.validate');
            Route::delete('/{bonCommande}', [BonCommandeController::class, 'destroy'])->name('bon-commandes.destroy');
            Route::put('/{bonCommande}/rejet', [BonCommandeController::class, 'rejectBonCommande'])->name('bon-commandes.reject');

            // Gestion des articles du bon de commande
            Route::get('/{bonCommande}/articles', [BonCommandeController::class, 'getArticles'])->name('bon-commandes.articles');
            Route::delete('/{bonCommande}/articles/{article}', [BonCommandeController::class, 'removeArticle'])->name('bon-commandes.articles.remove');

            // Génération de code
            Route::get('/generate-code', [BonCommandeController::class, 'generateCode'])->name('bon-commandes.generate-code');

            // Route::get('/bon-commandes/{bonCommande}/articles', [BonCommandeController::class, 'getArticles']);

            // Import/Export
            Route::get('/template/download', [BonCommandeController::class, 'downloadTemplate'])->name('bon-commandes.template');
            Route::post('/import', [BonCommandeController::class, 'import'])->name('bon-commandes.import');
            Route::get('/{bonCommande}/export', [BonCommandeController::class, 'export'])->name('bon-commandes.export');

            // Export PDF
            Route::get('/{bonCommande}/{bon_object}/pdf', [BonCommandeController::class, 'generatePDF'])->name('bon-commandes.pdf');
            Route::get('/{bonCommande}/excel', [BonCommandeController::class, 'generateExcel'])->name('bon-commandes.excel');

            // Recherche et filtres
            Route::get('/search', [BonCommandeController::class, 'search'])->name('bon-commandes.search');
            Route::get('/filter', [BonCommandeController::class, 'filter'])->name('bon-commandes.filter');

            // API pour les composants dynamiques
            Route::get('/programmations/{programmation}/lignes', [BonCommandeController::class, 'getLignesProgrammation'])
                ->name('bon-commandes.programmation.lignes');
            Route::get('/articles/{article}/prix', [BonCommandeController::class, 'getPrixArticle'])
                ->name('bon-commandes.article.prix');
        });

        // Routes pour les lignes de bon de commande
        Route::prefix('ligne-bon-commandes')->group(function () {
            // CRUD de base
            Route::post('/', [LigneBonCommandeController::class, 'store'])->name('ligne-bon-commandes.store');
            Route::put('/{ligne}', [LigneBonCommandeController::class, 'update'])->name('ligne-bon-commandes.update');
            Route::delete('/{ligne}', [LigneBonCommandeController::class, 'destroy'])->name('ligne-bon-commandes.destroy');

            // Opérations en lot
            Route::post('/bulk', [LigneBonCommandeController::class, 'bulkStore'])->name('ligne-bon-commandes.bulk.store');
            Route::put('/bulk', [LigneBonCommandeController::class, 'bulkUpdate'])->name('ligne-bon-commandes.bulk.update');

            // Récupération des lignes par bon de commande
            Route::get('/bon-commande/{bonCommande}', [LigneBonCommandeController::class, 'getByBonCommande'])
                ->name('ligne-bon-commandes.by-bon-commande');

            // Calculs et validations
            Route::post('/calculate', [LigneBonCommandeController::class, 'calculateMontant'])
                ->name('ligne-bon-commandes.calculate');
            Route::post('/validate', [LigneBonCommandeController::class, 'validateLignes'])
                ->name('ligne-bon-commandes.validate');
        });

        Route::prefix('factures')->group(function () {
            // Routes CRUD principales
            Route::get('/', [FactureFournisseurController::class, 'index'])->name('factures.index');
            Route::post('/', [FactureFournisseurController::class, 'store'])->name('factures.store');
            Route::get('/create', [FactureFournisseurController::class, 'create'])->name('factures.create');
            Route::get('/{facture}', [FactureFournisseurController::class, 'show'])->name('factures.show');
            Route::get('/details/{facture}', [FactureFournisseurController::class, 'details'])->name('factures.details');
            Route::put('/{facture}', [FactureFournisseurController::class, 'update'])->name('factures.update');
            Route::delete('/{facture}', [FactureFournisseurController::class, 'destroy'])->name('factures.destroy');
            Route::get('/{facture}/edit', [FactureFournisseurController::class, 'edit'])->name('factures.edit');
            Route::put('/{facture}/rejet', [FactureFournisseurController::class, 'rejectFacture'])->name('factures.reject');

            // Routes de validation et statuts
            Route::post('/{facture}/validate', [FactureFournisseurController::class, 'validated'])->name('factures.validate');
            Route::patch('/{facture}/statut-livraison', [FactureFournisseurController::class, 'updateDeliveryStatus'])->name('factures.delivery-status');
            Route::patch('/{facture}/statut-paiement', [FactureFournisseurController::class, 'updatePaymentStatus'])->name('factures.payment-status');

            // Routes d'impression
            Route::get('/{facture}/imprimer', [FactureFournisseurController::class, 'printFacture'])->name('factures.print');
            Route::get('/{facture}/export-excel', [FactureFournisseurController::class, 'exportExcel'])->name('factures.export-excel');
        });

        Route::prefix('reglements')->group(function () {
            // Routes CRUD principales
            Route::get('/', [ReglementFournisseurController::class, 'index'])->name('reglements.index');
            Route::post('/', [ReglementFournisseurController::class, 'store'])->name('reglements.store');
            Route::get('/{reglement}', [ReglementFournisseurController::class, 'show'])->name('reglements.show');
            Route::put('/{reglement}', [ReglementFournisseurController::class, 'update'])->name('reglements.update');
            Route::delete('/{reglement}', [ReglementFournisseurController::class, 'destroy'])->name('reglements.destroy');

            // Route de validation
            Route::post('/{reglement}/validate', [ReglementFournisseurController::class, 'validateReglement'])
                ->name('reglements.validate');

            // Route d'impression
            Route::get('/{reglement}/print', [ReglementFournisseurController::class, 'print'])
                ->name('reglements.print');

            // Routes de filtrage
            Route::get('/filter', [ReglementFournisseurController::class, 'filter'])
                ->name('reglements.filter');
        });

        Route::prefix('livraisons')->group(function () {
            // Routes principales CRUD
            Route::get('/', [BonLivraisonFournisseurController::class, 'index'])
                ->name('livraisons.index');

            Route::post('/', [BonLivraisonFournisseurController::class, 'store'])
                ->name('livraisons.store');

            Route::get('/create', [BonLivraisonFournisseurController::class, 'create'])
                ->name('livraisons.create');

            Route::get('/{bonLivraison}', [BonLivraisonFournisseurController::class, 'show'])
                ->name('livraisons.show');

            Route::get('/{bonLivraison}/edit', [BonLivraisonFournisseurController::class, 'edit'])
                ->name('livraisons.edit');

            Route::put('/{bonLivraison}', [BonLivraisonFournisseurController::class, 'update'])
                ->name('livraisons.update');

            Route::delete('/{bonLivraison}', [BonLivraisonFournisseurController::class, 'destroy'])
                ->name('livraisons.destroy');

            // Route de validation
            Route::post('/{bonLivraison}/validate', [BonLivraisonFournisseurController::class, 'validate_bon'])
                ->name('livraisons.validate');

            // Route de rejet
            Route::put('/{bonLivraison}/rejet', [BonLivraisonFournisseurController::class, 'reject'])
                ->name('livraisons.reject');

            // Routes d'impression et export
            Route::get('/{bonLivraison}/imprimer', [BonLivraisonFournisseurController::class, 'printLivraison'])
                ->name('livraisons.print');

            Route::get('/{bonLivraison}/export-excel', [BonLivraisonFournisseurController::class, 'exportExcel'])
                ->name('livraisons.export-excel');

            // Gestion des lignes de livraison
            Route::prefix('{bonLivraison}/lignes')->group(function () {
                Route::post('/', [LigneBonLivraisonFournisseurController::class, 'store'])
                    ->name('livraisons.lignes.store');

                Route::put('/{ligne}', [LigneBonLivraisonFournisseurController::class, 'update'])
                    ->name('livraisons.lignes.update');

                Route::delete('/{ligne}', [LigneBonLivraisonFournisseurController::class, 'destroy'])
                    ->name('livraisons.lignes.destroy');
            });

            // Routes de recherche et filtre
            Route::get('/search', [BonLivraisonFournisseurController::class, 'search'])
                ->name('livraisons.search');

            Route::get('/filter', [BonLivraisonFournisseurController::class, 'filter'])
                ->name('livraisons.filter');

            // Routes pour le template et l'import
            Route::get('/template/download', [BonLivraisonFournisseurController::class, 'downloadTemplate'])
                ->name('livraisons.template');

            Route::post('/import', [BonLivraisonFournisseurController::class, 'import'])
                ->name('livraisons.import');

            // Routes pour gestion par lot
            Route::post('/bulk', [LigneBonLivraisonFournisseurController::class, 'bulkStore'])
                ->name('livraisons.lignes.bulk.store');

            Route::put('/bulk', [LigneBonLivraisonFournisseurController::class, 'bulkUpdate'])
                ->name('livraisons.lignes.bulk.update');

            // Génération de code
            Route::get('/generate-code', [BonLivraisonFournisseurController::class, 'generateCode'])
                ->name('livraisons.generate-code');

            // APIs pour les composants dynamiques
            Route::get('/factures/{facture}/lignes', [BonLivraisonFournisseurController::class, 'getLignesFacture'])
                ->name('livraisons.facture.lignes');

            Route::get('/articles/{article}/prix', [BonLivraisonFournisseurController::class, 'getPrixArticle'])
                ->name('livraisons.article.prix');
        });

        // Routes des rapports
        Route::prefix('reports-fact')->group(function () {
            Route::get('/factures-stats', [FactureFournisseurController::class, 'factureStats'])->name('factures.stats');
            Route::get('/factures-impayes', [FactureFournisseurController::class, 'facturesImpayes'])->name('factures.impayes');
            Route::get('/analyse-paiements', [FactureFournisseurController::class, 'analysePaiements'])->name('factures.analyse-paiements');
        });

        // APPROVISIONNEMENTS
        Route::resource("/approvisionnements", FournisseurApprovisionnementController::class);
        Route::get("/approvisionnements/{id}/rejeter", [FournisseurApprovisionnementController::class, "rejeter"])->name("approvisionnements.rejeter");
        Route::get("/approvisionnements/{id}/validate", [FournisseurApprovisionnementController::class, "valider"])->name("approvisionnements.valider");
    });

    Route::prefix('vente')->group(function () {

        // Clients
        Route::prefix('clients')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('vente.clients.index');
            Route::get('/client-revendeur', [ClientController::class, 'clientRevendeur'])->name('vente.clients.clientRevendeur');
            Route::post('/', [ClientController::class, 'store'])->name('vente.clients.store');
            Route::get('/refresh-list', [ClientController::class, 'refreshList'])->name('vente.clients.refresh-list');
            Route::get('/{client}', [ClientController::class, 'show'])->name('vente.clients.show');
            Route::put('/{client}', [ClientController::class, 'update'])->name('vente.clients.update');
            Route::delete('/{client}', [ClientController::class, 'destroy'])->name('vente.clients.destroy');
            Route::get('/{client}/historique', [ClientController::class, 'historique'])->name('vente.clients.historique');
            Route::get('/{client}/factures', [ClientController::class, 'factures'])->name('vente.clients.factures');
            Route::get('/{client}/reglements', [ClientController::class, 'reglements'])->name('vente.clients.reglements');

            Route::get('/template/download', [ClientController::class, 'downloadTemplate'])
                ->name('vente.clients.template.download');

            Route::post('/import', [ClientController::class, 'import'])->name('vente.clients.import');
        });

        // RECOUVREMENTS 
        Route::controller(RecouvrementController::class)->prefix("recouvrement")->group(function () {
            Route::get("/index", "index")->name("recouvrement.index");
            Route::post("/store", "store")->name("recouvrement.store");
            Route::post("/verification", "verification")->name("recouvrement.verification");
        });

        // Routes pour les acomptes
        Route::prefix('acomptes')->group(function () {
            // Liste des acomptes
            Route::get('/', [AcompteClientController::class, 'index'])
                ->name('vente.acomptes.index');

            // Création d'un nouvel acompte
            Route::post('/', [AcompteClientController::class, 'store'])
                ->name('vente.acomptes.store');

            // Rafraîchissement de la liste des acomptes (pour AJAX)
            Route::get('/refresh-list', [AcompteClientController::class, 'refreshList'])
                ->name('vente.acomptes.refresh-list');

            // Voir les détails d'un acompte
            Route::get('/{acompte}', [AcompteClientController::class, 'show'])
                ->name('vente.acomptes.show')
                ->where('acompte', '[0-9]+');

            // Supprimer un acompte
            Route::delete('/{acompte}', [AcompteClientController::class, 'destroy'])
                ->name('vente.acomptes.destroy')
                ->where('acompte', '[0-9]+');

            // Routes pour les filtres et recherches via API
            Route::prefix('api')->group(function () {
                // Options pour le select2 des clients
                Route::get('/clients/select-options', [AcompteClientController::class, 'getClientOptions'])
                    ->name('vente.acomptes.client-options');

                // Vérification du solde client
                Route::get('/clients/{client}/solde', [AcompteClientController::class, 'getClientSolde'])
                    ->name('vente.acomptes.client-solde');
            });

            // Routes pour les impressions et exports
            Route::prefix('print')->group(function () {
                // Imprimer un reçu d'acompte
                Route::get('/{acompte}/recu', [AcompteClientController::class, 'printRecu'])
                    ->name('vente.acomptes.print-recu');

                // Export de la liste des acomptes
                Route::get('/export', [AcompteClientController::class, 'export'])
                    ->name('vente.acomptes.export');
            });

            // Routes pour les rapports
            Route::prefix('rapports')->group(function () {
                // Rapport des acomptes par période
                Route::get('/periode', [AcompteClientController::class, 'rapportPeriode'])
                    ->name('vente.acomptes.rapport-periode');

                // Rapport des acomptes par client
                Route::get('/client/{client}', [AcompteClientController::class, 'rapportClient'])
                    ->name('vente.acomptes.rapport-client');

                // Statistiques des acomptes
                Route::get('/stats', [AcompteClientController::class, 'getStats'])
                    ->name('vente.acomptes.stats');
            });
        });

        // sessions
        Route::prefix('sessions')->group(function () {
            // Liste des sessions
            Route::get('/', [SessionCaisseController::class, 'index'])->name('vente.sessions.index');

            // Créer une nouvelle session
            Route::post('/', [SessionCaisseController::class, 'store'])->name('vente.sessions.store');

            // Voir les détails d'une session
            Route::get('/{sessionCaisse}', [SessionCaisseController::class, 'show'])->name('vente.sessions.show');

            // Fermer une session
            Route::post('/{sessionCaisse}/fermer', [SessionCaisseController::class, 'fermer'])->name('vente.sessions.fermer');

            // Générer le rapport d'une session
            Route::get('/{sessionCaisse}/rapport', [SessionCaisseController::class, 'rapport'])->name('vente.sessions.rapport');

            // Détails du comptage d'une session
            Route::get('/{sessionCaisse}/comptage', [SessionCaisseController::class, 'getComptage'])->name('vente.sessions.comptage');

            // Sauvegarder les détails du comptage
            Route::post('/{sessionCaisse}/comptage', [SessionCaisseController::class, 'saveComptage'])->name('vente.sessions.save-comptage');

            // Stats et résumé de la session
            Route::get('/{sessionCaisse}/stats', [SessionCaisseController::class, 'getStats'])->name('vente.sessions.stats');

            // Export des données de la session (ex: Excel)
            Route::get('/{sessionCaisse}/export', [SessionCaisseController::class, 'export'])->name('vente.sessions.export');

            // Ventes par session
            Route::get('/{sessionId}/ventes', [SessionCaisseController::class, 'ventesBySession'])->name('ventes.sessions.list-ventes');

            // Encaisser une vente
            Route::put('vente/{facture}/encaisser', [SessionCaisseController::class, 'encaisser'])->name('vente.sessions.encaisser');
        });

        // Routes pour les factures
        Route::prefix('factures')->group(function () {
            // Liste des factures
            Route::get('/', [FactureClientController::class, 'index'])->name('vente.facture.index');

            // Créer une nouvelle facture
            Route::post('/store', [FactureClientController::class, 'store'])->name('vente.facture.store');

            Route::put('/update/{id}', [FactureClientController::class, 'update'])->name('vente.facture.update');

            // Voir les détails d'une facture
            Route::get('/{id}', [FactureClientController::class, 'show'])->name('vente.facture.show');

            // Routes pour la recherche d'articles et récupération des données
            Route::prefix('api')->group(function () {
                Route::get('/articles/search', [FactureClientController::class, 'searchArticles']);
            });

            Route::prefix('articles')->group(function () {
                Route::get('/{articleId}/tarifs', [FactureClientController::class, 'getTarifs']);
                Route::get('/{articleId}/unites', [FactureClientController::class, 'getUnites']);
            });

            Route::post('/{id}/validate', [FactureClientController::class, 'validateFacture'])->name('vente.facture.validate');
            Route::delete('/{id}/delete', [FactureClientController::class, 'destroy'])->name('vente.facture.delete');
            Route::get('/{facture}/print', [FactureClientController::class, 'print'])->name('vente.facture.print');

            Route::put('/{id}/update', [FactureClientController::class, 'update'])->name('vente.facture.update')->where('id', '[0-9]+');

            // LES PROFORMA
            Route::resource('proforma', ProformaController::class);
            Route::get('/generate-proforma/{id}', [ProformaController::class, 'generatePDF'])->name("generate-proforma");
            Route::get('/validate-proforma/{id}', [ProformaController::class, 'valider'])->name("validate-proforma");
        });

        // Routes pour les reglements
        Route::prefix('reglement')->group(function () {
            // Liste des factures
            Route::get('/', [ReglementClientController::class, 'index'])->name('vente.reglement.index');

            // Créer une nouvelle facture
            Route::post('/store', [ReglementClientController::class, 'store'])->name('vente.reglement.store');

            // Voir les détails d'une facture
            Route::get('/{id}', [ReglementClientController::class, 'show'])->name('vente.reglement.show');
        });

        Route::prefix('reglement')->group(function () {
            // Liste des règlements
            Route::get('/', [ReglementClientController::class, 'index'])
                ->name('vente.reglement.index');

            // Créer un nouveau règlement
            Route::post('/store', [ReglementClientController::class, 'store'])
                ->name('vente.reglement.store');

            // Voir les détails d'un règlement
            Route::get('/{id}/details', [ReglementClientController::class, 'details'])
                ->name('vente.reglement.details');

            // Valider un règlement
            Route::post('/{reglementClient}/validate', [ReglementClientController::class, 'validate_reglement'])
                ->name('vente.reglement.validate');

            // Supprimer un règlement
            Route::delete('/{reglement}', [ReglementClientController::class, 'destroy'])
                ->name('vente.reglement.destroy');

            // Route pour rafraîchir la liste des règlements
            Route::get('/refresh/list', [ReglementClientController::class, 'refreshList'])
                ->name('vente.reglement.refresh');

            Route::put('/{id}/update', [ReglementClientController::class, 'update'])
                ->name('reglement.update')
                ->where('id', '[0-9]+');

            Route::post('/{id}/cancel', [ReglementClientController::class, 'cancel'])
                ->name('reglement.cancel')
                ->where('id', '[0-9]+');

            Route::get('/refresh', [ReglementClientController::class, 'refreshList'])
                ->name('refresh');
        });

        Route::prefix('livraisons')->group(function () {
            Route::get('/verifier-stock', [LivraisonClientController::class, 'verifierStock'])->name('vente.livraisons.verifier-stock');

            // Liste des livraisons
            Route::get('/', [LivraisonClientController::class, 'index'])
                ->name('vente.livraisons.index');

            // Créer une nouvelle livraison
            Route::post('/', [LivraisonClientController::class, 'store'])->name('vente.livraisons.store');

            // Voir les détails d'une livraison
            Route::get('/{livraisonClient}', [LivraisonClientController::class, 'show'])
                ->name('vente.livraisons.show');

            // Modifier une livraison
            Route::get('/{livraisonClient}/edit', [LivraisonClientController::class, 'edit'])
                ->name('vente.livraisons.edit');

            Route::put('/{livraisonClient}', [LivraisonClientController::class, 'update'])
                ->name('vente.livraisons.update');

            // Supprimer une livraison
            Route::delete('/{livraisonClient}', [LivraisonClientController::class, 'destroy'])->name('vente.livraisons.destroy');

            // Valider une livraison
            Route::post('{livraisonClient}/validate', [LivraisonClientController::class, 'validateLivraison'])->name('vente.livraisons.validate');

            Route::post('/{livraisonClient}/validate', [LivraisonClientController::class, 'validateLivraison'])
                ->name('vente.livraisons.validate');

            // Annuler une livraison
            Route::post('/{livraisonClient}/annuler', [LivraisonClientController::class, 'annuler'])
                ->name('vente.livraisons.annuler');

            // Imprimer une livraison
            Route::get('/{livraisonClient}/print', [LivraisonClientController::class, 'print'])
                ->name('vente.livraisons.print');

            // Gestion des lignes de livraison
            Route::post('/{livraisonClient}/lignes', [LigneLivraisonClientController::class, 'store'])
                ->name('vente.livraisons.lignes.store');

            Route::put('/{livraisonClient}/lignes/{ligneLivraison}', [LigneLivraisonClientController::class, 'update'])
                ->name('vente.livraisons.lignes.update');

            Route::delete('/{livraisonClient}/lignes/{ligneLivraison}', [LigneLivraisonClientController::class, 'destroy'])
                ->name('vente.livraisons.lignes.destroy');

            // Récupérer les lignes de facture disponibles pour livraison
            Route::get('/facture/{factureClient}/lignes-disponibles', [LivraisonClientController::class, 'getLignesFactureDisponibles'])
                ->name('vente.livraisons.lignes-facture-disponibles');

            // Export des livraisons
            Route::get('/export', [LivraisonClientController::class, 'export'])
                ->name('vente.livraisons.export');

            Route::get('/{livraisonClient}/export', [LivraisonClientController::class, 'exportOne'])
                ->name('vente.livraisons.export-one');

            // Stats et tableaux de bord
            Route::get('/stats', [LivraisonClientController::class, 'getStats'])
                ->name('vente.livraisons.stats');

            // Routes pour les mouvements de stock liés
            Route::get('/{livraisonClient}/mouvements', [LivraisonClientController::class, 'getMouvements'])
                ->name('vente.livraisons.mouvements');

            // Routes pour la traçabilité
            Route::get('/{livraisonClient}/historique', [LivraisonClientController::class, 'getHistorique'])
                ->name('vente.livraisons.historique');

            Route::get('/pdf/a4/bon-livrasion/{facture}', [LivraisonClientController::class, 'generateBonA4'])
                ->name('vente.livraisons.pdf.bon-livraison');

            Route::get('/pdf/a5/bon-livrasion/{facture}', [LivraisonClientController::class, 'generateBonA5'])
                ->name('vente.livraisons.pdf.bon-livraison');
        });

        // REQUTES
        Route::resource("requetes", RequeteController::class);
        Route::post('/valider-requete/{id}', [RequeteController::class, 'validateRequete'])->name('valider-requete');

        // TRANSPORTS
        Route::resource('transports', TransportController::class);
        Route::post('/valider-transport/{id}', [TransportController::class, 'validateRequete'])->name('valider-transport');
    });

    Route::prefix('revendeurs')->group(function () {
        // Routes pour les factures
        Route::prefix('factures')->group(function () {
            // Liste des factures
            Route::get('/', [FactureRevendeurController::class, 'index'])->name('revendeur.facture.index');

            // Créer une nouvelle facture
            Route::post('/store', [FactureRevendeurController::class, 'store'])->name('revendeur.facture.store');

            // Voir les détails d'une facture
            Route::get('/{id}', [FactureRevendeurController::class, 'show'])->name('revendeur.facture.show');

            // Routes pour la recherche d'articles et récupération des données
            Route::prefix('api')->group(function () {
                Route::get('/articles/search', [FactureRevendeurController::class, 'searchArticles']);
            });

            Route::prefix('articles')->group(function () {
                Route::get('/{articleId}/tarifs', [FactureRevendeurController::class, 'getTarifs']);
                Route::get('/{articleId}/unites', [FactureRevendeurController::class, 'getUnites']);
            });

            Route::post('/{id}/validate', [FactureRevendeurController::class, 'validateFacture'])->name('revendeur.facture.validate');
            Route::delete('/{id}/delete', [FactureRevendeurController::class, 'destroy'])->name('revendeur.facture.delete');
            Route::get('/{facture}/print', [FactureRevendeurController::class, 'print'])->name('revendeur.facture.print');
            Route::put('/{facture}/update', [FactureRevendeurController::class, 'update'])->name('revendeur.facture.update');
        });

        Route::get('/validation', [FactureRevendeurController::class, 'dailyRapport'])->name('revendeur.normale.rapport');
        Route::put('/make-validation', [FactureRevendeurController::class, 'MakevalidationDaily'])->name('revendeur.normale.makevalidation');

        Route::prefix('ventes-speciales')->group(function () {
            // Liste des factures
            Route::get('/', [SpecialController::class, 'index'])->name('revendeur.speciales.index');

            // Créer une nouvelle facture
            Route::post('/store', [SpecialController::class, 'store'])->name('revendeur.speciales.store');

            // Voir les détails d'une facture
            Route::get('/{id}', [SpecialController::class, 'show'])->name('revendeur.speciales.show');

            // Routes pour la recherche d'articles et récupération des données
            Route::prefix('api')->group(function () {
                Route::get('/articles/search', [SpecialController::class, 'searchArticles']);
            });

            Route::prefix('articles')->group(function () {
                Route::get('/{articleId}/tarifs', [SpecialController::class, 'getTarifs']);
                Route::get('/{articleId}/unites', [SpecialController::class, 'getUnites']);
            });

            Route::post('/{id}/validate', [SpecialController::class, 'validateFacture'])->name('revendeur.speciales.validate');
            Route::delete('/{id}/delete', [SpecialController::class, 'destroy'])->name('revendeur.speciales.delete');
            Route::get('/{facture}/print', [SpecialController::class, 'print'])->name('revendeur.speciales.print');

            Route::get('/rapport/validation', [SpecialController::class, 'specialeRapport'])->name('revendeur.speciale.rapport');
            Route::put('/make-validation/{facture}', [SpecialController::class, 'MakeSellvalidation'])->name('revendeur.speciale.makevalidation');
        });
    });

    Route::prefix('rapports')->middleware(['auth'])->group(function () {

        Route::get('/rapports/compte-client', [RapportVenteController::class, 'rapportCompteClient'])->name('rapports.compte-client');

        Route::get('/suivies-ventes', [RapportVenteController::class, 'suivieVente'])
            ->name('rapports.etat-ventes');

        Route::get('/ventes-articles', [RapportVenteController::class, 'ventesParArticle'])
            ->name('rapports.ventes-articles');

        Route::get('/ventes-articles/export', [RapportVenteController::class, 'exportVentesArticles'])
            ->name('vente.rapports.ventes-articles.export');

        Route::get('/ventes-familles', [RapportVenteController::class, 'ventesParFamille'])
            ->name('rapports.ventes-familles');

        Route::get('/ventes-clients', [RapportVenteController::class, 'ventesParClient'])
            ->name('rapports.ventes-clients');

        Route::get('/stock-mouvements', [RapportStockController::class, 'mouvementReport'])
            ->name('rapports.mouvement-stock');

        Route::get('/stock-dispo', [RapportStockController::class, 'rapportStockDisponible'])
            ->name('rapports.stock-dispo');

        Route::get('/ventes-journalier', [RapportVenteController::class, 'ventesJournalieres'])
            ->name('rapports.vente-journaliere');

        // LES ENREGISTREMENTS NON VALIDES
        Route::get('/enregistrement-non-valides', [RapportVenteController::class, '_enregistrementsNonValides'])
            ->name('rapports._enregistrementsNonValides');

        // Route::post('rapports/stock/change-depot', 'RapportController@changeDepot')->name('rapports.stock.changeDepot');
        Route::get('/stock-alert', [StockAlertController::class, 'index'])
            ->name('rapports.alert-stock');

        // Route principale pour la session ouverte
        Route::get('/rapports/sessions/rapport', [RapportVenteController::class, 'sessionVente'])
            ->name('vente.sessions.rapport');

        // Route pour une session spécifique
        Route::get('/rapports/sessions/{session?}/rapport', [RapportVenteController::class, 'sessionVente'])
            ->name('vente.sessions.rapport.show');
        Route::prefix('stock/valorisation')->group(function () {
            Route::get('/', [RapportValorisationController::class, 'index'])->name('stock.valorisation.index');
            Route::get('/export', [RapportValorisationController::class, 'export'])->name('stock.valorisation.export');
            Route::get('/article-history/{article}/{depot}', [RapportValorisationController::class, 'getArticleHistory'])
                ->name('stock.valorisation.history');

            Route::get('/fiche-stock/{article}/{depot}', [RapportValorisationController::class, 'printFicheStock'])
                ->name('stock.valorisation.fiche');
        });


        Route::prefix('stock/rotation')->middleware(['auth'])->group(function () {
            // Page principale de la rotation des stocks
            Route::get('/', [StockRotationController::class, 'index'])
                ->name('stock.rotation.index');

            // Détails de rotation pour un article/magasin spécifique
            Route::get('/details/{article}/{depot}', [StockRotationController::class, 'getRotationDetails'])
                ->name('stock.rotation.details');

            // Évolution du stock sur une période
            Route::get('/evolution/{article}/{depot}', [StockRotationController::class, 'getEvolutionData'])
                ->name('stock.rotation.evolution');

            // Export des données de rotation
            Route::get('/export', [StockRotationController::class, 'export'])
                ->name('stock.rotation.export');

            // Rapport PDF des articles à faible rotation
            Route::get('/rapport/faible-rotation', [StockRotationController::class, 'generateLowRotationReport'])
                ->name('stock.rotation.report.low');

            // Rapport PDF des articles dormants
            Route::get('/rapport/articles-dormants', [StockRotationController::class, 'generateDormantReport'])
                ->name('stock.rotation.report.dormant');

            // API pour le graphique d'évolution
            Route::get('/api/graph-data/{article}/{depot}', [StockRotationController::class, 'getGraphData'])
                ->name('stock.rotation.graph');

            // Analyse comparative
            Route::get('/analyse-comparative', [StockRotationController::class, 'compareRotations'])
                ->name('stock.rotation.compare');

            // Prévisions et recommandations
            Route::get('/previsions', [StockRotationController::class, 'getPredictions'])
                ->name('stock.rotation.predictions');
        });

        Route::prefix('stock/alert')->middleware(['auth'])->group(function () {
            // Page principale de la rotation des stocks
            Route::get('/', [StockAlertController::class, 'index'])
                ->name('stock.alerte.index');
        });

        Route::get('/dashboard-vente', [RapportVenteController::class, 'index'])->name('rapports.dashboard-vente');
        // Route::post('/{id}/validate', [FactureRevendeurController::class, 'validateFacture'])->name('revendeur.facture.validate');
        // Route::delete('/{id}/delete', [FactureRevendeurController::class, 'destroy'])->name('revendeur.facture.delete');
        // Route::get('/{facture}/print', [FactureRevendeurController::class, 'print'])->name('revendeur.facture.print');
    });

    Route::prefix('creances')->group(function () {
        Route::get('/', [RapportCreanceController::class, 'index'])
            ->name('rapports.creances.index');

        Route::get('/details/{client}', [RapportCreanceController::class, 'detailsClient'])
            ->name('rapports.creances.client');

        Route::get('/filter', [RapportCreanceController::class, 'filter'])
            ->name('rapports.creances.filter');

        Route::prefix('export')->group(function () {
            Route::get('/', [RapportCreanceController::class, 'export'])
                ->name('rapports.creances.export');

            Route::get('/client/{client}', [RapportCreanceController::class, 'exportClient'])
                ->name('rapports.creances.export-client');
        });

        Route::get('/statistiques', [RapportCreanceController::class, 'getStats'])
            ->name('rapports.creances.stats');
    });

    Route::prefix('rapports/stock')->name('rapports.stock.')->group(function () {
        Route::get('/mouvements', [RapportStockController::class, 'mouvementReport'])->name('mouvements');
        Route::get('/stock-dispo', [RapportStockController::class, 'rapportStockDisponible'])->name('stock-dispo');
        Route::post('/change-depot', [RapportStockController::class, 'changeDepot'])->name('changeDepot');
        Route::get('/export', [RapportStockController::class, 'export'])->name('export');
        Route::get('/export-mouvements', [RapportStockController::class, 'exportMouvements'])->name('export-mouvements');
        Route::get('/print', [RapportStockController::class, 'print'])->name('print');
    });

    Route::prefix('rapports')->group(function () {
        Route::get('/programmations', [RapportAchatController::class, 'rapportProgrammations'])->name('rapports.pre-commandes');

        Route::get('/resume-mensuel', [RapportAchatController::class, 'resumeMensuel']);

        Route::get('/rapports/programmations', [RapportAchatController::class, 'rapportBonCommandes'])->name('rapports.bon-commandes');
        Route::get('/rapports/factures', [RapportAchatController::class, 'rapportFactures'])->name('rapports.facture-achats');
        Route::get('/rapports/livraisons', [RapportAchatController::class, 'rapportLivraisons'])->name('rapports.livraison-achats');
        Route::get('/rapports/reglements', [RapportAchatController::class, 'rapportReglements'])->name('rapports.reglement-achats');
        Route::get('/rapports/compte', [RapportAchatController::class, 'rapportCompteFournisseur'])->name('rapports.compte-fournisseur');


        Route::get('/rapports/programmations/export', [RapportAchatController::class, 'exportProgrammations'])->name('rapports.programmations.export');
        Route::post('soldes/import', [SoldeInitialFournisseurController::class, 'import'])->name('soldes.import');
        Route::post('soldes/import-client', [SoldeInitialClientController::class, 'import'])->name('soldes.import-client');

        Route::post('soldes-client/import', [SoldeInitialClientController::class, 'import'])
            ->name('rapports.soldes.client.import');
    });

    // Routes API pour les détails des factures et règlements
    Route::prefix('api')->group(function () {
        Route::get('/factures-client/{facture}', [FactureClientController::class, 'getDetailsFacture'])
            ->name('api.factures.details');

        Route::get('/reglements-client/{reglement}', [ReglementClientController::class, 'getDetailsReglement'])
            ->name('api.reglements.details');
    });
});
