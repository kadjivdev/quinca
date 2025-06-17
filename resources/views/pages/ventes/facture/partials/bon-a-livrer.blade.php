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
            background: #f8f9fa;
        }

        .company-info {
            float: left;
            width: 35%;
            padding: 15px;
            border-radius: 5px;
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
            border: 2px solid #2c3e50!important;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    @if($entete)
    <div class="header px-1">
        <div class="company-info">
            <img src="{{public_path('head_facture.jpg')}}" width="250" height="100" class="form-control" alt="" srcset="">
        </div>

        <div class="invoice-details">
            <img src="{{public_path('kadjiv.jpeg')}}" width="250" height="100" class="form-control" alt="" srcset="">
        </div>
        <div class="clearfix"></div>
    </div>
    @endif

    <br><br><br><br><br>
    <div class="client-info">
        <h3> <strong class="livraison-number">BON DE LIVRAISON : {{$facture->numero}} </strong>  | <strong class="livraison-date"> DATE : {{Carbon\Carbon::parse($facture->date_facture)->locale('fr')->isoFormat('D MMMM YYYY')}} </strong></h3>
        <p> <strong>Client:</strong>  {{$facture->client?->raison_sociale}} </p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="text-center">N° D'ORDRE</th>
                <th class="text-center">ARTICLES</th>
                <th class="text-center">QUANTITE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facture->lignes as $ligne)
            <tr>
                <td class="text-center">{{$loop->index + 1}}</td>
                <td class="text-center">{{ $ligne->article->designation }}</td>
                <td class="text-center">{{ number_format($ligne->quantite, 3, ',', ' ') }} {{$ligne->uniteVente?->libelle_unite}} </td>
            </tr>
            @endforeach
        </tbody>
        <br><br><br><br><br>
        <tfoot class="mt-5">
            <tr>
                <td class="text-center">CLIENT</td>
                <td class="text-center">CONTROLEUR</td>
                <td class="text-center">COMMERCIAL</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>