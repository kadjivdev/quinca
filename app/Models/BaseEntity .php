<?php

abstract class BaseEntity {
    // Propriétés principales
    protected $id;
    protected $date_creation;
    protected $user_creation;
    protected $date_modification;
    protected $user_modification;
    protected $date_validation;
    protected $user_validation;
    protected $est_valide;
    protected $est_supprime;
    protected $statut_validation;
    protected $date_rejet;
    protected $user_rejet;
    protected $motif_rejet;

    // Constantes pour les statuts
    const STATUT_BROUILLON = 'BROUILLON';
    const STATUT_EN_ATTENTE_VALIDATION = 'EN_ATTENTE_VALIDATION';
    const STATUT_VALIDE = 'VALIDE';
    const STATUT_REJETE = 'REJETE';

    public function __construct() {
        $this->date_creation = date('Y-m-d H:i:s');
        $this->date_modification = date('Y-m-d H:i:s');
        $this->statut_validation = self::STATUT_BROUILLON;
        $this->est_valide = false;
        $this->est_supprime = false;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getDateCreation() {
        return $this->date_creation;
    }

    public function getUserCreation() {
        return $this->user_creation;
    }

    public function getStatutValidation() {
        return $this->statut_validation;
    }

    public function getEstValide() {
        return $this->est_valide;
    }

    public function getEstSupprime() {
        return $this->est_supprime;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setUserCreation($user) {
        $this->user_creation = $user;
    }

    // Méthodes de gestion du cycle de vie
    public function demanderValidation($user) {
        if ($this->statut_validation === self::STATUT_BROUILLON) {
            $this->date_modification = date('Y-m-d H:i:s');
            $this->user_modification = $user;
            $this->statut_validation = self::STATUT_EN_ATTENTE_VALIDATION;
            return true;
        }
        return false;
    }

    public function valider($user) {
        if ($this->statut_validation === self::STATUT_EN_ATTENTE_VALIDATION) {
            $this->date_validation = date('Y-m-d H:i:s');
            $this->user_validation = $user;
            $this->statut_validation = self::STATUT_VALIDE;
            $this->est_valide = true;
            return true;
        }
        return false;
    }

    public function rejeter($user, $motif) {
        if ($this->statut_validation === self::STATUT_EN_ATTENTE_VALIDATION) {
            $this->date_rejet = date('Y-m-d H:i:s');
            $this->user_rejet = $user;
            $this->motif_rejet = $motif;
            $this->statut_validation = self::STATUT_REJETE;
            return true;
        }
        return false;
    }

    public function modifier($user) {
        $this->date_modification = date('Y-m-d H:i:s');
        $this->user_modification = $user;
        if ($this->statut_validation === self::STATUT_VALIDE) {
            $this->statut_validation = self::STATUT_EN_ATTENTE_VALIDATION;
            $this->est_valide = false;
        }
    }

    public function supprimer($user) {
        $this->date_modification = date('Y-m-d H:i:s');
        $this->user_modification = $user;
        $this->est_supprime = true;
    }

    public function restaurer($user) {
        $this->date_modification = date('Y-m-d H:i:s');
        $this->user_modification = $user;
        $this->est_supprime = false;
    }
}
