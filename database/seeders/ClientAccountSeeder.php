<?php

namespace Database\Seeders;

use App\Models\Vente\Client;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientAccounts = [
            "SALAOU MACHOUDOU" => -2475440,
            "BOUKARY ISSIAKA" => 0,
            "DG ALELUYA" => 0,
            "TOP OFFSET" => -2297709,
            "ANIWANOU PIERRE" => 0,
            "AMIDATH TOUKOUROU" => -560,
            "HOUNSOU JACQUELINE" => 0,
            "BANKOLE ELIE" => 0,
            "ETS KOBOSLAIN" => 2558,
            "ETS AKE PLUS" => 0,
            "ADAH ROBERT" => 0,
            "GBEVO ALINE" => 0,
            "COGED" => -909500,
            "SAMB ET CO" => -49944,
            "ETS HOUETCHENOU" => -42280,
            "ANCHIROU FOUSSENI" => 0,
            "BOKOVOU RAMATOU" => 0,
            "AGENT CHARLE" => -495200,
            "AGENT OLIVE" => -4659000,
            "AGENT JOHANESS" => -14578936,
            "LOKONON ANTOINE" => 0,
            "GOUMAGBE DAMIENNE" => 0,
            "OSSENI FEMI" => 0,
            "AMEGANH GLORIA" => 0,
            "EACT" => -1041998,
            "YASSIROU ATCHAMOU" => 0,
            "LEBOR" => -13685,
            "AGOSSA LAZARE" => -4500,
            "JEAN DJOUGOU" => -464300,
            "HOTEL KOB" => 0,
            "XIU AMG QUAN" => 0,
            "OUSSA JEREMIE" => 0,
            "MAMAN ZUL" => -71060,
            "SOHOUN BERNARD" => 51,
            "DOSSOU EDWIGE" => 0,
            "COCO GEBA" => 0,
            "ARISTIDE MPO" => 0,
            "BOURAIMA FATIOU" => 21000,
            "SEYDOU MOHAMED" => -1512650,
            "ORTHINIEL GROUPE" => -1957,
            "FELIX TONAHI" => 0,
            "PERCE DIVINE" => -1499400,
            "JEAN BAPTISTE DJOCHOU" => 0,
            "HABIB VIGAN" => 0,
            "ETS AMJW" => 0,
            "GILBERT AVAMEY" => 0,
            "GERICOS" => 0,
            "KOUANDE JULES" => 0,
            "NASSIF TETTEDE" => 0,
            "ALI RAZACK" => 28800,
            "ETS NOBISEF" => -2415000,
            "MADAME GODONOU" => 0,
            "ZANNOU LUCIEN" => 0,
            "MOUSSA ABOUBACAR" => 0,
            "BTB SARL" => -25599580,
            "GUELIFFO BARTHELEMI" => 0,
            "INSTITUT BIBLIQUE DU BENIN" => 0,
            "CHAFFA MAURICE BONI" => 0,
            "ETS MISSIN JAH" => 0,
            "DOSSOU SAMUEL" => -2,
            "HOUSSOU BLANDIN" => 0,
            "GUEZO PASCAL" => 0,
            "YACOUBOU OUSMANE" => -5752200,
            "AKUESSON KOFFI" => -69500,
            "ZINSOU DAMIEN" => 0,
            "GLELE JEANNETTE" => 0,
            "JUSTIN HOUDJI" => 1950,
            "MOUBINOU TANKPINOU" => 0,
            "ALLOCHEDE BIOU BERNARD" => 0,
            "MME LATI" => 0,
            "ASSOGBA GILLES" => 0,
            "MR BANQUIER" => 0,
            "CELESTIN DJOSSE" => 0,
            "DJIVOEDO ERNEST" => 0,
            "DOSSA NICOLAS" => 0,
            "GNONLONFOUN JOSE" => 0,
            "ANZY PARAISO" => 0,
            "DOSSOU YOVO SALOMON" => 0,
            "KOUGBE EMILE" => 0,
            "ETS DAGBEDJI" => 0,
            "ALAIN K. (AGENT ENTRETIENT)" => -20900,
            "AITCHEHOU SERVICE" => 0,
            "AROUNA TADJOU" => -2030598,
            "ETS DASSIBE" => 0,
            "KINTOKONOU DARIUS" => 0,
            "REQUETTE 05" => 595000,
            "SOUNNOUVI EMMANUEL" => 0,
            "DOSSA AGOSSI" => 0,
            "INOUSSA RACHID" => 0,
            "PICS ROMARIC" => -20,
            "MAHUDJRO SERVICE" => 25,
            "BADAROU KAFILATOU" => 0,
            "ASSOGBA EULOGE" => 700,
            "ALADJI BAKA" => 0,
            "AHOLO MARCEL" => 0,
            "DAZAN GILBERT" => 0,
            "HOUNDJENOUKON CLEMENT" => 0,
            "HODO JOSEPH" => 0,
            "AFRI CONTACT BENIN" => 0,
            "BONOU JANVIER" => 0,
            "YEHOUENOU JEAN BAPTISTE" => 0,
            "AMOUR ET PAIX" => -71060,
            "KINVOEDO DAMIEN" => 0,
            "DOSSOU CONSTANCE" => 0,
            "ALADJI SALOU" => -120000,
            "HOUENOU RICHARD" => 0,
            "KEDOTE EMILE (LITTORAL)" => -8843,
            "PSF (LITTORAL)" => -40,
            "AHOUNOU FRANCIS (LITTORAL)" => -26400,
            "HOUESSOU JACOB" => 0,
            "AGOSSOU CLEMENT" => 0
        ];

        try {
            DB::beginTransaction();
            foreach ($clientAccounts as $name => $solde) {
                $client = Client::firstWhere("raison_sociale", $name);
                if ($client && $client->acomptes()) {
                    $client->acomptes()
                        ->create(
                            [
                                "date" => Carbon::now(),
                                "reference" => Str::uuid(),
                                "montant" => $solde,
                                "type_paiement" => 'virement',
                                "statut" => 'valide',
                                "created_by" => 1,
                                "point_de_vente_id" => 1,
                                "validated_by" => 1,
                                "validated_at" => Carbon::now(),
                            ]
                        );
                }
            }
            DB::commit();
            echo "Mise à jour des account clients éffectué avec succes";
        } catch (\Exception $e) {
            echo "error......";
            echo $e->getMessage();
        }
    }
}
