<?php

namespace Database\Seeders;

use App\Models\Achat\Fournisseur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FournisseurAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fournisseurSoldes = [
            "0B1G1R" => 0,
            "0M9UFX" => 0,
            "0TCP94" => 0,
            "110Q8X" => 0,
            "16JM5R" => 0,
            "193KRI" => 0,
            "1LAOQ9" => 0,
            "24YLC9" => 0,
            "2SDPZD" => 0,
            "2TTB98" => 0,
            "2X84T4" => 0,
            "2ZGE6M" => 0,
            "313UG0" => 0,
            "3B88OH" => 0,
            "3SQO76" => 0,
            "5CEIKS" => 0,
            "5GWA1R" => 0,
            "5TBYA7" => 0,
            "5WEQF0" => 0,
            "6CKONX" => 242867616,
            "6FW9UD" => 0,
            "7XJUKE" => 0,
            "8CSWJA" => 0,
            "8SZXKT" => 0,
            "9NZRB8" => 0,
            "BAE3FA" => 0,
            "BMQG9G" => 0,
            "BYE3GS" => 0,
            "CQ8IZR" => 0,
            "CTN83B" => 0,
            "CVNY9Z" => 0,
            "CXW8ZE" => 3010000,
            "DC3LIL" => 0,
            "EW76Q7" => 0,
            "F4RCCX" => 0,
            "F4SHO9" => 0,
            "FWXFX2" => 54200000,
            "G9158X" => 0,
            "G9CYUL" => 0,
            "GA8GRP" => 0,
            "HOR82E" => 0,
            "I56NHQ" => 0,
            "IFX9TR" => 0,
            "IK1BEB" => 0,
            "K5HTD4" => 0,
            "KEDSJK" => 0,
            "KJZZOM" => 7500000,
            "KS8TXY" => 0,
            "LL7GPP" => 0,
            "MANTWN" => 0,
            "N3L3D2" => 0,
            "N56LLL" => 4400000,
            "NG166G" => 806000,
            "NTZMYE" => 0,
            "O2GY5U" => 0,
            "O5QYT8" => 0,
            "OIKI98" => 0,
            "OZRA3U" => 0,
            "PACBVB" => 0,
            "PI7SES" => 0,
            "Q3TWAW" => 0,
            "Q9H5QW" => 0,
            "RJTC2Z" => 0,
            "RUTU4S" => 0,
            "RY9Q2Q" => 0,
            "S2GA98" => 0,
            "SY7EGD" => 0,
            "TQ5NYF" => 0,
            "VR70PJ" => 0,
            "VRVQEU" => 0,
            "W58RHT" => 0,
            "W5SB0I" => 0,
            "W68HKH" => 0,
            "WB7A96" => 1344700,
            "WK5DAK" => 198611801,
            "XFFQG0" => 0,
            "XRVPGF" => 0,
            "XTG0V5" => 0,
            "Y5XHDA" => -537400,
            "Z81VCW" => 8917000,
            "ZFHR4G" => 0,
            "ZLRJ3H" => 0,
            "ZM7T7V" => 0,
        ];

        try {
            DB::beginTransaction();
            foreach ($fournisseurSoldes as $code => $solde) {
                $fournisseur = Fournisseur::firstWhere("code_fournisseur", $code);
                if ($fournisseur) {
                    $fournisseur->accomptes()
                        ->create(
                            [
                                "date" => Carbon::now(),
                                "montant" => $solde,
                                'created_by' => 1,
                                'type_paiement' => 'virement',
                                'reference' => Str::uuid(),
                                'point_de_vente_id' => 1,
                                'observation' => "Account effectué lors de la migration",
                                "statut" => 'valide',
                                'validated_at' => now(),
                                'validated_by' => 1,
                                'created_by' => 1
                            ]
                        );
                }
            }
            DB::commit();
            echo "Insersion des accomptes éffectué avec succes";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
