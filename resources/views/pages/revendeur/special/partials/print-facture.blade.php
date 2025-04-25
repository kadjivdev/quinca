<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .company-info {
            float: left;
            width: 60%;
        }
        .company-info h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .invoice-details {
            float: right;
            width: 35%;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .invoice-details h1 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 24px;
        }
        .client-info {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            font-size: 11px;
        }
        td {
            padding: 8px;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-section {
            float: right;
            width: 350px;
            margin-top: 20px;
        }
        .total-section table {
            background: #f8f9fa;
        }
        .total-section table tr:last-child {
            background: #2c3e50;
            color: white;
        }
        .clearfix { clear: both; }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            padding: 20px 0;
            font-size: 10px;
            border-top: 2px solid #eee;
        }

        .normalisation-info {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }

        .qr-container {
            text-align: center;
            margin-top: 15px;
        }

        .qr-code {
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }

        .signature-section {
            margin-top: 40px;
            border-top: 1px dashed #ddd;
            padding-top: 20px;
        }


    </style>

<style>
    .normalisation-info {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        text-align: center;
    }

    .qr-container {
        text-align: center;
        margin-top: 15px;
    }

    .qr-container svg {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
</style>


</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>QuincaKadjiv</h2>
            {{-- Ajoutez vos informations d'entreprise --}}
            <p>Cotonou - Benin<br>
               Téléphone: +229 212 345 678<br>
               Email: contact@QuincaKadjiv.org</p>
        </div>

        <div class="invoice-details">
            <h1>FACTURE</h1>
            <p>
                <strong>N° : </strong>{{ $facture->numero }}<br>
                <strong>Date : </strong>{{ $facture->date_facture->format('d/m/Y') }}<br>
                <strong>Échéance : </strong>{{ $facture->date_echeance->format('d/m/Y') }}<br>
                @if($facture->statut === 'valide')
                <strong>Date validation : </strong>{{ $facture->date_validation->format('d/m/Y') }}<br>
                @endif
            </p>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="client-info">
        <h3>CLIENT</h3>
        <p>
            <strong>{{ $facture->client->raison_sociale }}</strong><br>
            {{ $facture->client->adresse ?? 'N/A' }}<br>
            Tel: {{ $facture->client->telephone ?? 'N/A' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Prix Unit. HT</th>
                <th class="text-right">Remise (%)</th>
                <th class="text-right">Montant HT</th>
                <th class="text-right">TVA</th>
                <th class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->lignes as $ligne)
            <tr>
                <td>{{ $ligne->article->designation }}</td>
                <td class="text-right">{{ number_format($ligne->quantite, 3, ',', ' ') }}</td>

                <td class="text-right">{{ number_format($ligne->prix_unitaire_ht, 3, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne->taux_remise, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne->montant_ht_apres_remise, 3, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne->montant_tva, 3, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne->montant_ttc, 3, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table>
            <tr>
                <td><strong>Total HT</strong></td>
                <td class="text-right">{{ number_format($facture->montant_ht, 3, ',', ' ') }}</td>
            </tr>
            <tr>
                <td><strong>Remise globale</strong></td>
                <td class="text-right">{{ number_format($facture->montant_remise, 3, ',', ' ') }}</td>
            </tr>
            <tr>
                <td><strong>HT après remise</strong></td>
                <td class="text-right">{{ number_format($facture->montant_ht_apres_remise, 3, ',', ' ') }}</td>
            </tr>
            <tr>
                <td><strong>TVA ({{ $facture->taux_tva }}%)</strong></td>
                <td class="text-right">{{ number_format($facture->montant_tva, 3, ',', ' ') }}</td>
            </tr>
            @if($facture->taux_aib > 0)
            <tr>
                <td><strong>AIB ({{ $facture->taux_aib }}%)</strong></td>
                <td class="text-right">{{ number_format($facture->montant_aib, 3, ',', ' ') }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total TTC</strong></td>
                <td class="text-right"><strong>{{ number_format($facture->montant_ttc, 3, ',', ' ') }}</strong></td>
            </tr>
            <tr>
                <td><strong>Montant réglé</strong></td>
                <td class="text-right">{{ number_format($facture->montant_regle, 3, ',', ' ') }}</td>
            </tr>
            <tr>
                <td><strong>Reste à payer</strong></td>
                <td class="text-right">{{ number_format($facture->reste_a_regler, 3, ',', ' ') }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    @if($facture->notes)
    <div style="margin-top: 20px;">
        <strong>Notes :</strong>
        <p>{{ $facture->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <div class="signature-section">
            <p>
                Facture créée par: {{ $facture->createdBy->name }} le {{ $facture->created_at->format('d/m/Y H:i') }}
                @if($facture->validated_by)
                <br>Validée par: {{ $facture->validatedBy->name }} le {{ $facture->date_validation->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>

        @if($facture->numero_normalise)
<div class="normalisation-info">
    <div style="display: inline-block; text-align: left;">
        <p style="margin: 5px 0;">
            <strong>Facture Normalisée</strong><br>
            N° Compteur: {{ $facture->numero_normalise }}<br>
            Code: {{ $facture->numero_compteur }}<br>
            Date Normalisation: {{ $facture->date_normalisation->format('d/m/Y H:i') }}
        </p>
    </div>

    <div class="qr-container">
        {!! QrCode::size(100)
            ->generate(json_encode([
                'Numéro' => $facture->numero_normalise,
                'UID' => $facture->uid_normalisation,
                'Date' => $facture->date_normalisation->format('Y-m-d H:i:s'),
                'Montant' => number_format($facture->montant_ttc, 2),
                'Client' => $facture->client->raison_sociale
            ]))
        !!}
    </div>
</div>
@endif
    </div>
</body>
</html>
