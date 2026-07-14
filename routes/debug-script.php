// // Capitulation des qte entrée dans le Magasin 2 Cotonou depuis le 28 Mars 2026 jusqu'à maintenant
    // StockDepot::query()
    //     ->where("depot_id", 4)
    //     ->get()
    //     ->each(function ($stock) {
    //         $livraisonFournisseurLignes = LigneBonLivraisonFournisseur::query()
    //             // ->with("bonLivraison", "article", "uniteMesure")
    //             ->where("article_id", $stock->article_id)
    //             ->whereHas("bonLivraison", function ($query) use ($stock) {
    //                 $query
    //                     ->where("depot_id", $stock->depot_id)
    //                     ->whereBetween("validated_at", [
    //                         Carbon::create(2026, 02, 28)->startOfDay(), // 23 Mars
    //                         now(), // maintenant
    //                     ]);
    //             })
    //             ->get()
    //             ->transform(function ($ligne) {
    //                 // 
    //                 $serviceStockEntree = new ServiceStockEntree();
    //                 $conversion = $serviceStockEntree->rechercherConversion(
    //                     $ligne->unite_mesure_id,
    //                     $ligne->article->unite_mesure_id,
    //                     $ligne->article->id
    //                 );

    //                 if (!$conversion) {
    //                     throw new Exception(sprintf(
    //                         "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
    //                         $ligne->uniteMesure?->libelle_unite ?? '---',
    //                         $ligne->article->uniteMesure?->libelle_unite ?? '---',
    //                         $ligne->article->code_article ?? '---'
    //                     ));
    //                 }
    //                 // qte de base
    //                 $quantite_base = $serviceStockEntree->convertirQuantite(
    //                     $ligne->quantite,
    //                     $conversion,
    //                     $ligne->unite_mesure_id
    //                 );

    //                 return [
    //                     // "id" => $ligne->id,
    //                     "bordereau" => $ligne->bonLivraison->code ?? '---',
    //                     "code_article" => $ligne->article->code_article ?? '---',
    //                     "depot" => $ligne->bonLivraison->depot?->libelle_depot ?? '---',
    //                     "quantite" => $ligne->quantite,
    //                     "qte_base" => $quantite_base,
    //                     "unite_mesure" => $ligne->uniteMesure?->libelle_unite ?? '---',
    //                     "unite_mesure_base" => $ligne->article->uniteMesure?->libelle_unite ?? '---',
    //                     "validated_at" => $ligne->bonLivraison->validated_at ?? '---',
    //                 ];
    //             })

    //             ->groupBy('code_article')
    //             ->map(function ($group) {
    //                 return $group->sum('qte_base');
    //             });

    //         $lastInventaire = $stock->article->lastInventaireDetail($stock->depot_id);

    //         $stock->qteDepart = $lastInventaire?->qte_reel ?? 0;
    //         $stock->code_article = $stock->article?->code_article ?? '---';

    //         // qteDepart
    //         $stock->qte_base_total = $stock->qteDepart + $livraisonFournisseurLignes->sum();

    //         return $stock;
    //     })
    //     ->groupBy('code_article')
    //     ->map(function ($group) {
    //         Log::debug("Group: ", ["stock" => $group]);

    //         $stock = StockDepot::findOrFail($group->first()->id);
    //         // mise à jour de la qte reelle du stock
    //         $stock->quantite_reelle = $group->sum('qte_base_total');
    //         $stock->save();

    //         return $group->sum('qte_base_total');
    //     });


    // // // retour des marchandises à ajouter aux stocks
    // LigneMarchandise::with("marchandBack", "marchandBack.depot", "article")
    //     ->where("quantite", ">", 0)
    //     ->whereHas("marchandBack", function ($query) {
    //         $query->where("depot_id", 4)
    //             ->whereNotNull("validated_by")
    //             ->whereBetween("created_at", [
    //                 Carbon::create(2026, 02, 28)->startOfDay(), // 28 Mars
    //                 now(), // maintenant
    //             ]);
    //     })
    //     ->get()
    //     ->transform(function ($ligne) {
    //         $serviceStockEntree = new ServiceStockEntree();
    //         $conversion = $serviceStockEntree->rechercherConversion(
    //             $ligne->unite_vente_id,
    //             $ligne->article->unite_mesure_id,
    //             $ligne->article->id
    //         );

    //         if (!$conversion) {
    //             throw new Exception(sprintf(
    //                 "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
    //                 $ligne->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->code_article ?? '---'
    //             ));
    //         }

    //         // qte de base
    //         $quantite_base = $serviceStockEntree->convertirQuantite(
    //             $ligne->quantite,
    //             $conversion,
    //             $ligne->unite_vente_id
    //         );

    //         $ligne->qte_base = $quantite_base;
    //         $ligne->bordereau = $ligne->marchandBack?->numero ?? '---';
    //         $ligne->depot = $ligne->marchandBack?->depot;
    //         $ligne->code_article = $ligne->article?->code_article ?? '---';
    //         $ligne->unite_mesure = $ligne->uniteMesure?->libelle_unite ?? '---';
    //         $ligne->unite_mesure_base = $ligne->article?->uniteMesure?->libelle_unite ?? '---';
    //         return $ligne;
    //     })
    //     ->groupBy('code_article')
    //     ->map(function ($lignes) {
    //         Log::debug("Data :", ["lignes" => $lignes->toArray()]);

    //         $stock = StockDepot::firstWhere([
    //             'article_id' => $lignes->first()?->article_id,
    //             "depot_id" => $lignes->first()?->depot?->id
    //         ]);

    //         if (!$stock) {
    //             return "Stock non trouvé pour l'article: " . $lignes->first()?->code_article . " dans le depot: " . $lignes->first()?->depot?->libelle_depot;
    //         }
    //         Log::debug("Stock trouvé:", ["stock" => $stock]);

    //         // mise à jour de la qte reelle du stock
    //         $stock->quantite_reelle += $lignes->sum('qte_base');
    //         $stock->save();

    //         return [
    //             "code_article" => $lignes->first()->code_article,
    //             "total_quantite" => $lignes->sum("quantite"),
    //             "total_qte_base" => $lignes->sum("qte_base"),
    //         ];
    //     });

    // // livraison client | qte à ajouter aux  stocks
    // LigneLivraisonClient::with("livraison", "livraison.depot", "article")
    //     ->whereHas("livraison", function ($query) {
    //         $query->where("depot_dest_id", 3)
    //             ->whereIn("numero", ["BL-260602-f9e", "BL-260630-e37"]) /// les bons concernés
    //             ->whereNotNull("validated_by")
    //             ->whereBetween("created_at", [
    //                 Carbon::create(2026, 03, 23)->startOfDay(), // 23 Mars
    //                 now(), // maintenant
    //             ])
    //         ;
    //     })
    //     ->get()
    //     ->transform(function ($ligne) {
    //         $serviceStockEntree = new ServiceStockEntree();
    //         $conversion = $serviceStockEntree->rechercherConversion(
    //             $ligne->unite_vente_id,
    //             $ligne->article->unite_mesure_id,
    //             $ligne->article->id
    //         );

    //         if (!$conversion) {
    //             throw new Exception(sprintf(
    //                 "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
    //                 $ligne->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->code_article ?? '---'
    //             ));
    //         }

    //         // qte de base
    //         $quantite_base = $serviceStockEntree->convertirQuantite(
    //             $ligne->quantite,
    //             $conversion,
    //             $ligne->unite_vente_id
    //         );

    //         $ligne->qte_base = $quantite_base;
    //         $ligne->bordereau = $ligne->livraison?->numero ?? '---';
    //         $ligne->depot = $ligne->livraison?->depotDestination;
    //         $ligne->code_article = $ligne->article?->code_article ?? '---';
    //         $ligne->unite_mesure = $ligne->uniteMesure?->libelle_unite ?? '---';
    //         $ligne->unite_mesure_base = $ligne->article?->uniteMesure?->libelle_unite ?? '---';

    //         return $ligne;
    //     })
    //     ->groupBy('code_article')
    //     ->map(function ($lignes) {
    //         Log::debug("Data :", ["lignes" => $lignes->toArray()]);

    //         $stock = StockDepot::firstWhere([
    //             'article_id' => $lignes->first()?->article_id,
    //             "depot_id" => $lignes->first()?->depot?->id
    //         ]);

    //         Log::debug("Stock debut:", ["stock" => $stock]);
    //         if (!$stock) {
    //             return "Stock non trouvé pour l'article: " . $lignes->first()?->code_article . " dans le depot: " . $lignes->first()?->depot?->libelle_depot;
    //         }

    //         // mise à jour de la qte reelle du stock
    //         $stock->quantite_reelle += $lignes->sum("qte_base");
    //         $stock->save();

    //         Log::debug("Stock finale:", ["stock" => $stock]);

    //         return [
    //             "code_article" => $lignes->first()->code_article,
    //             "total_quantite" => $lignes->sum("quantite"),
    //             "total_qte_base" => $lignes->sum("qte_base"),
    //         ];
    //     });


    // // livraison client | qte à récupérer des stocks
    // LigneLivraisonClient::with("livraison", "livraison.depot", "article")
    //     ->whereHas("livraison", function ($query) {
    //         $query->where("depot_dest_id", 4)
    //             ->whereIn("numero", ["BL-260421-95b"]) /// les bons concernés
    //             ->whereNotNull("validated_by")
    //             ->whereBetween("created_at", [
    //                 Carbon::create(2026, 02, 28)->startOfDay(), // 23 Mars
    //                 now(), // maintenant
    //             ])
    //         ;
    //     })
    //     ->get()
    //     ->transform(function ($ligne) {
    //         $serviceStockEntree = new ServiceStockEntree();
    //         $conversion = $serviceStockEntree->rechercherConversion(
    //             $ligne->unite_vente_id,
    //             $ligne->article->unite_mesure_id,
    //             $ligne->article->id
    //         );

    //         if (!$conversion) {
    //             throw new Exception(sprintf(
    //                 "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
    //                 $ligne->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->code_article ?? '---'
    //             ));
    //         }

    //         // qte de base
    //         $quantite_base = $serviceStockEntree->convertirQuantite(
    //             $ligne->quantite,
    //             $conversion,
    //             $ligne->unite_vente_id
    //         );

    //         $ligne->qte_base = $quantite_base;
    //         $ligne->bordereau = $ligne->livraison?->numero ?? '---';
    //         $ligne->depot = $ligne->livraison?->depotDestination;
    //         $ligne->code_article = $ligne->article?->code_article ?? '---';
    //         $ligne->unite_mesure = $ligne->uniteMesure?->libelle_unite ?? '---';
    //         $ligne->unite_mesure_base = $ligne->article?->uniteMesure?->libelle_unite ?? '---';

    //         return $ligne;
    //     })
    //     ->groupBy('code_article')
    //     ->map(function ($lignes) {
    //         Log::debug("Data :", ["lignes" => $lignes->toArray()]);

    //         $stock = StockDepot::firstWhere([
    //             'article_id' => $lignes->first()?->article_id,
    //             "depot_id" => $lignes->first()?->depot?->id
    //         ]);

    //         Log::debug("Stock debut:", ["stock" => $stock]);
    //         if (!$stock) {
    //             return "Stock non trouvé pour l'article: " . $lignes->first()?->code_article . " dans le depot: " . $lignes->first()?->depot?->libelle_depot;
    //         }

    //         // mise à jour de la qte reelle du stock
    //         $stock->quantite_reelle -= $lignes->sum("qte_base");
    //         $stock->save();

    //         Log::debug("Stock finale:", ["stock" => $stock]);

    //         return [
    //             "code_article" => $lignes->first()->code_article,
    //             "total_quantite" => $lignes->sum("quantite"),
    //             "total_qte_base" => $lignes->sum("qte_base"),
    //         ];
    //     });

    // // livraison fournisseur | qte à recuperer des stocks
    // LigneBonLivraisonFournisseur::with("bonLivraison", "bonLivraison.depot", "article")
    //     ->whereHas("bonLivraison", function ($query) {
    //         $query->where("depot_id", 3)
    //             ->whereIn("code", ["BLF2604220002", "BLF2604220003"]) /// les bons concernés
    //             ->whereNotNull("validated_by")
    //             ->whereBetween("validated_at", [
    //                 Carbon::create(2026, 03, 23)->startOfDay(), // 23 Mars
    //                 now(), // maintenant
    //             ])
    //         ;
    //     })
    //     ->get()
    //     ->transform(function ($ligne) {
    //         $serviceStockEntree = new ServiceStockEntree();
    //         $conversion = $serviceStockEntree->rechercherConversion(
    //             $ligne->unite_mesure_id,
    //             $ligne->article->unite_mesure_id,
    //             $ligne->article->id
    //         );

    //         if (!$conversion) {
    //             throw new Exception(sprintf(
    //                 "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
    //                 $ligne->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->uniteMesure?->libelle_unite ?? '---',
    //                 $ligne->article->code_article ?? '---'
    //             ));
    //         }

    //         // qte de base
    //         $quantite_base = $serviceStockEntree->convertirQuantite(
    //             $ligne->quantite,
    //             $conversion,
    //             $ligne->unite_mesure_id
    //         );

    //         $ligne->qte_base = $quantite_base;
    //         $ligne->code = $ligne->bonLivraison?->code ?? '---';
    //         $ligne->depot = $ligne->bonLivraison?->depot;
    //         $ligne->code_article = $ligne->article?->code_article ?? '---';
    //         $ligne->unite_mesure = $ligne->uniteMesure?->libelle_unite ?? '---';
    //         $ligne->unite_mesure_base = $ligne->article?->uniteMesure?->libelle_unite ?? '---';

    //         return $ligne;
    //     })
    //     ->groupBy('code_article')
    //     ->map(function ($lignes) {
    //         Log::debug("Data :", ["lignes" => $lignes->toArray()]);

    //         $stock = StockDepot::firstWhere([
    //             'article_id' => $lignes->first()?->article_id,
    //             "depot_id" => $lignes->first()?->depot?->id
    //         ]);

    //         Log::debug("Stock debut:", ["stock" => $stock]);
    //         if (!$stock) {
    //             return "Stock non trouvé pour l'article: " . $lignes->first()?->code_article . " dans le depot: " . $lignes->first()?->depot?->libelle_depot;
    //         }

    //         // // mise à jour de la qte reelle du stock
    //         $stock->quantite_reelle -= $lignes->sum("qte_base");
    //         $stock->save();

    //         Log::debug("Stock finale:", ["stock" => $stock]);

    //         return [
    //             "code_bordereau" => $lignes->first()->code,
    //             "code_article" => $lignes->first()->code_article,
    //             "total_quantite" => $lignes->sum("quantite"),
    //             "total_qte_base" => $lignes->sum("qte_base"),
    //         ];
    //     });