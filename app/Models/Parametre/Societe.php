<?php

namespace App\Models\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Societe extends Model
{
   use SoftDeletes;

   protected $fillable = [
       'nom_societe',
       'raison_sociale',
       'forme_juridique',
       'rccm',
       'ifu',
       'rib',
       'email',
       'telephone_1',
       'telephone_2',
       'adresse',
       'ville',
       'pays',
       'description',
       'logo_path',
       'favicon_path',
       'parametres_supplementaires'
   ];

   protected $casts = [
       'parametres_supplementaires' => 'array'
   ];

   // Accesseur pour obtenir l'URL complète du logo
   public function getLogoUrlAttribute()
   {
       return $this->logo_path ? asset('storage/' . $this->logo_path) : asset('images/default-logo.png');
   }

   // Accesseur pour obtenir l'URL complète du favicon
   public function getFaviconUrlAttribute()
   {
       return $this->favicon_path ? asset('storage/' . $this->favicon_path) : asset('images/default-favicon.ico');
   }

   // Accesseur pour les paramètres de facturation
   public function getFacturationParametersAttribute()
   {
       return $this->parametres_supplementaires['facturation'] ?? [];
   }

   // Accesseur pour un paramètre spécifique TVA
   public function getTvaAttribute()
   {
       return $this->parametres_supplementaires['facturation']['tva'] ?? '18';
   }

   // Accesseur pour la devise
   public function getDeviseAttribute()
   {
       return $this->parametres_supplementaires['facturation']['devise'] ?? 'FCFA';
   }

   // Méthode utilitaire pour mettre à jour un paramètre spécifique
   public function updateParameter($key, $value)
   {
       $params = $this->parametres_supplementaires ?? [];
       data_set($params, $key, $value);
       $this->parametres_supplementaires = $params;
       $this->save();
   }

   // Méthode pour obtenir les paramètres d'impression
   public function getImpressionParametersAttribute()
   {
       return $this->parametres_supplementaires['impression'] ?? [
           'format_papier' => 'A4',
           'entete_personnalise' => true,
           'pied_page_personnalise' => true,
           'afficher_logo' => true,
           'couleur_principale' => '#000000',
           'police' => 'Arial'
       ];
   }

   // Méthode pour obtenir les paramètres de communication
   public function getCommunicationParametersAttribute()
   {
       return $this->parametres_supplementaires['communication'] ?? [
           'signature_email' => '',
           'whatsapp' => '',
           'facebook' => '',
           'site_web' => ''
       ];
   }

   // Méthode pour formater le numéro de téléphone
   public function getFormattedPhone1Attribute()
   {
       return $this->formatPhoneNumber($this->telephone_1);
   }

   public function getFormattedPhone2Attribute()
   {
       return $this->telephone_2 ? $this->formatPhoneNumber($this->telephone_2) : null;
   }

   // Méthode privée pour formater les numéros de téléphone
   private function formatPhoneNumber($number)
   {
       // Retire tous les caractères non numériques
       $number = preg_replace('/[^0-9]/', '', $number);

       // Format béninois : XX XX XX XX XX
       if (strlen($number) === 8) {
           return substr($number, 0, 2) . ' ' .
                  substr($number, 2, 2) . ' ' .
                  substr($number, 4, 2) . ' ' .
                  substr($number, 6, 2);
       }

       return $number;
   }

   // Méthode pour obtenir l'adresse complète formatée
   public function getFullAddressAttribute()
   {
       $address = [];

       if ($this->adresse) {
           $address[] = $this->adresse;
       }
       if ($this->ville) {
           $address[] = $this->ville;
       }
       if ($this->pays) {
           $address[] = $this->pays;
       }

       return implode(', ', $address);
   }

   // Méthode pour vérifier si la Societe est complète
   public function isComplete()
   {
       return !empty($this->nom_societe) &&
              !empty($this->ifu) &&
              !empty($this->telephone_1) &&
              !empty($this->adresse);
   }
}
