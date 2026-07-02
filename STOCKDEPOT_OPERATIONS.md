# Opérations sur le modèle StockDepot

## Résumé
- **create()**: 2 occurrences
- **update()**: 2 occurrences (via instance)
- **save()**: 5 occurrences
- **Méthodes personnalisées**: 2 (`creer()`, `mettreAJour()`)

---

## 1. Opérations CREATE() - Création de StockDepot

### 1.1 [app/Services/ServiceStockEntree.php](app/Services/ServiceStockEntree.php#L104)
**Lieu**: Ligne 104  
**Contexte**: Création d'un nouveau stock lors d'une entrée en stock  
**Code**:
```php
$stock = StockDepot::create([
    'depot_id' => $donnees['depot_id'],
    'article_id' => $article->id,
    'user_id' => Auth::id(),
    'unite_mesure_id' => $unite_dest_id,
]);
```
**Flux**: 
- Appelé dans la méthode `traiterEntreeStock()`
- Conditionnel: seulement si le stock n'existe pas déjà
- Suivi d'un `$stock->save()` à la ligne 151 pour persister les modifications

---

### 1.2 [app/Services/ServiceStockSortie.php](app/Services/ServiceStockSortie.php#L108)
**Lieu**: Ligne 108  
**Contexte**: Création d'un nouveau stock lors d'une sortie en stock  
**Code**:
```php
$stock = StockDepot::create([
    'depot_id' => $donnees['depot_id'],
    'article_id' => $article->id,
    'user_id' => Auth::id(),
    'unite_mesure_id' => $unite_dest_id,
]);
```
**Flux**:
- Appelé dans la méthode `traiterSortieStock()`
- Conditionnel: seulement si le stock n'existe pas déjà
- Suivi d'un `$stock->save()` à la ligne 147 pour persister les modifications

---

## 2. Opérations UPDATE() - Mise à jour via instance

### 2.1 [app/Http/Controllers/Parametre/DepotController.php](app/Http/Controllers/Parametre/DepotController.php#L448)
**Lieu**: Ligne 448  
**Contexte**: Mise à jour de la quantité réelle lors d'un inventaire (check_all_article = true)  
**Code**:
```php
$stok_depot = StockDepot::find($stockDepot["id"]);
if (!$stok_depot) {
    throw new \Exception("Ce stock depot n'existe pas!");
}
$stok_depot->update(['quantite_reelle' => $request->all_qte_reel ?? $stok_depot->quantite_reelle]);
```
**Flux**:
- Appelé dans la méthode `generateInventaire()` 
- Met à jour la quantité réelle pour tous les articles du dépôt
- Boucle foreach sur `$depot->stocks`

---

### 2.2 [app/Http/Controllers/Parametre/DepotController.php](app/Http/Controllers/Parametre/DepotController.php#L450)
**Lieu**: Ligne 450  
**Contexte**: Mise à jour de la quantité réelle lors d'un inventaire (check_all_article = false)  
**Code**:
```php
$stok_depot = StockDepot::find($stockDepot["id"]);
if (!$stok_depot) {
    throw new \Exception("Ce stock depot n'existe pas");
}
$stok_depot->update(['quantite_reelle' => $stockDepot["qte_reel"] ?? $stok_depot->quantite_reelle]);
```
**Flux**:
- Appelé dans la méthode `generateInventaire()`
- Met à jour la quantité réelle pour les articles sélectionnés
- Boucle foreach sur `$stockDepotCheckeds`

---

## 3. Opérations SAVE() - Sauvegarde d'instances

### 3.1 [app/Models/Stock/StockDepot.php](app/Models/Stock/StockDepot.php#L231)
**Lieu**: Ligne 231 (méthode `reserver()`)  
**Contexte**: Réservation de quantité en stock  
**Code**:
```php
public function reserver(float $quantite): bool
{
    if ($quantite > $this->quantite_disponible) {
        throw new Exception("Quantité disponible insuffisante pour la réservation");
    }

    $this->quantite_reservee += $quantite;
    return $this->save();
}
```
**Flux**: Appelée quand une quantité doit être réservée

---

### 3.2 [app/Models/Stock/StockDepot.php](app/Models/Stock/StockDepot.php#L138)
**Lieu**: Ligne 138 (méthode `annulerReservation()`)  
**Contexte**: Annulation de réservation de quantité  
**Code**:
```php
public function annulerReservation(float $quantite): bool
{
    if ($quantite > $this->quantite_reservee) {
        throw new Exception("Impossible d'annuler plus que la quantité réservée");
    }

    $this->quantite_reservee -= $quantite;
    return $this->save();
}
```
**Flux**: Appelée quand une réservation doit être annulée

---

### 3.3 [app/Models/Stock/StockDepot.php](app/Models/Stock/StockDepot.php#L151)
**Lieu**: Ligne 151 (méthode `traiterMouvement()`)  
**Contexte**: Traitement d'un mouvement de stock (entrée, sortie, ajustement)  
**Code**:
```php
public function traiterMouvement(StockMouvement $mouvement): bool
{
    switch ($mouvement->type_mouvement) {
        case StockMouvement::TYPE_ENTREE:
            $this->traiterEntree($mouvement);
            break;
        case StockMouvement::TYPE_SORTIE:
            $this->traiterSortie($mouvement);
            break;
        case StockMouvement::TYPE_AJUSTEMENT:
            $this->traiterAjustement($mouvement);
            break;
        default:
            throw new Exception("Type de mouvement non géré");
    }

    $this->date_dernier_mouvement = now();
    return $this->save();
}
```
**Flux**: Appelée pour enregistrer les modifications suite à un mouvement

---

### 3.4 [app/Models/Stock/StockDepot.php](app/Models/Stock/StockDepot.php#L272)
**Lieu**: Ligne 272 (méthode `creer()`)  
**Contexte**: Création personnalisée d'un StockDepot avec validation  
**Code**:
```php
public static function creer(array $data, $user): self
{
    $stock = new self();
    
    $stock->depot_id = $data['depot_id'];
    $stock->article_id = $data['article_id'];
    $stock->quantite_reelle = $data['quantite_reelle'] ?? 0;
    $stock->quantite_reservee = $data['quantite_reservee'] ?? 0;
    $stock->prix_moyen = $data['prix_moyen'] ?? 0;
    // ... autres assignations ...
    $stock->user_id = $user->id;

    if (!$stock->validate()) {
        throw new Exception("Données du stock invalides");
    }

    $stock->save();
    return $stock;
}
```
**Flux**: Méthode alternative pour créer un StockDepot avec validation stricte

---

### 3.5 [app/Services/ServiceStockEntree.php](app/Services/ServiceStockEntree.php#L151)
**Lieu**: Ligne 151  
**Contexte**: Sauvegarde du stock après ajout de quantité lors d'une entrée  
**Code**:
```php
// 8. Mise à jour du stock
$stock->fill([
    'quantite_reelle' => $ancien_stock + $quantite_base,
    'prix_moyen' => $nouveau_cump ?? 0.00,
    'date_dernier_mouvement' => $donnees['date_mouvement'],
    'user_id' => $donnees['user_id'],
]);

$stock->save();
```
**Flux**:
- Appelée après le calcul du CUMP
- Met à jour quantité_reelle, prix_moyen, et date_dernier_mouvement

---

### 3.6 [app/Services/ServiceStockSortie.php](app/Services/ServiceStockSortie.php#L147)
**Lieu**: Ligne 147  
**Contexte**: Sauvegarde du stock après retrait de quantité lors d'une sortie  
**Code**:
```php
// 8. Mise à jour du stock
$stock->fill([
    'quantite_reelle' => $ancien_stock + $quantite_base,
    'prix_moyen' => 0.00,
    'date_dernier_mouvement' => $donnees['date_mouvement'],
    'user_id' => $donnees['user_id'],
]);

$stock->save();
```
**Flux**:
- Appelée après le calcul du nouveau stock
- Met à jour quantité_reelle, prix_moyen, et date_dernier_mouvement

---

### 3.7 [app/Models/Stock/StockDepot.php](app/Models/Stock/StockDepot.php#L292)
**Lieu**: Ligne 292 (méthode `mettreAJour()`)  
**Contexte**: Mise à jour personnalisée d'un StockDepot avec validation  
**Code**:
```php
public function mettreAJour(array $data, $user): bool
{
    foreach ($data as $key => $value) {
        if (in_array($key, $this->fillable)) {
            $this->$key = $value;
        }
    }

    $this->user_id = $user->id;

    if (!$this->validate()) {
        throw new Exception("Données du stock invalides");
    }

    return $this->save();
}
```
**Flux**: Méthode alternative pour mettre à jour un StockDepot avec validation stricte

---

## Tableau Synthétique

| Type | Fichier | Ligne(s) | Méthode/Contexte | Fréquence |
|------|---------|---------|------------------|-----------|
| CREATE | ServiceStockEntree.php | 104 | traiterEntreeStock() | Entrée en stock |
| CREATE | ServiceStockSortie.php | 108 | traiterSortieStock() | Sortie de stock |
| UPDATE | DepotController.php | 448 | generateInventaire() (all) | Inventaire complet |
| UPDATE | DepotController.php | 450 | generateInventaire() (partial) | Inventaire partiel |
| SAVE | StockDepot.php | 231 | reserver() | Réservation stock |
| SAVE | StockDepot.php | 138 | annulerReservation() | Annulation réservation |
| SAVE | StockDepot.php | 151 | traiterMouvement() | Après mouvement |
| SAVE | StockDepot.php | 272 | creer() | Création validée |
| SAVE | ServiceStockEntree.php | 151 | traiterEntreeStock() | Après ajout stock |
| SAVE | ServiceStockSortie.php | 147 | traiterSortieStock() | Après retrait stock |
| SAVE | StockDepot.php | 292 | mettreAJour() | Mise à jour validée |

---

## Points Importants

1. **Transactions DB**: Les services `ServiceStockEntree` et `ServiceStockSortie` utilisent `DB::beginTransaction()` et `DB::commit()`
2. **Validation**: Le modèle dispose d'une méthode `validate()` pour vérifier l'intégrité des données
3. **Calcul CUMP**: La mise à jour du prix moyen utilise un calcul CUMP (Coût Unitaire Moyen Pondéré)
4. **Unité de mesure**: Le stockage avec conversion d'unité est géré dans les services
5. **Réservation**: Mécanisme de réservation disponible via les méthodes `reserver()` et `annulerReservation()`
6. **Audit**: Chaque opération inclut l'ID utilisateur et la date du mouvement

