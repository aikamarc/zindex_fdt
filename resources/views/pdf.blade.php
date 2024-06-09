<div class="pdf_container">
    <div class="paddingPdf">

        <div class="titleZindex">
            <div class="titlePdf">Z-INDEX - Gestion du temps de travail</div>
            <div class="datePdf">{{ $mois }}-{{ $annee }}</div>
            <div class="identitePdf">@if($nom == null && $prenom == null) Nom Prénom @else {{ $nom }} {{ $prenom }} @endif</div>
        </div>

        <div class="zindexLogo">
            <img src="{{ asset('zindex.png') }}">
        </div>

        <div class="hrPDF"></div>

        <table class="top-table">
            <tr>
                <td width="16%" rowspan="3">Date</td>
                <td width="28%" colspan="4">Horaires Prévus</td>
                <td width="14%" rowspan="3">Cumul Prévu</td>
                <td width="28%" colspan="4">Horaires Réalisés</td>
                <td width="14%" rowspan="3">Cumul Réalisé</td>
            </tr>
            <tr>
                <td width="14%" colspan="2">Matin</td>
                <td width="14%" colspan="2">Après-Midi</td>
                <td width="14%" colspan="2">Matin</td>
                <td width="14%" colspan="2">Après-Midi</td>
            </tr>
            <tr>
                <td width="7%">Début</td>
                <td width="7%">Fin</td>
                <td width="7%">Début</td>
                <td width="7%">Fin</td>
                <td width="7%">Début</td>
                <td width="7%">Fin</td>
                <td width="7%">Début</td>
                <td width="7%">Fin</td>
            </tr>
        </table>

        @foreach($dates as $counter => $dateArray)
            <table class="temps-table">
            @foreach($dateArray as $key => $date)
                    <tr     @if($date['j_paye'])    style="background-color: #ffff00;"
                        @elseif($date['j_ferie'])   style="background-color: #ffb7f2;"
                        @elseif($date['j_maladie']) style="background-color: #00b050;"
                        @elseif($date['j_cours'])   style="background-color: #86b9ff;"
                        @endif
                    >
                        <td width="16%">{{ $date['dates'] }}</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $matin_debut }} @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $matin_fin }}   @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $soir_debut }}  @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $soir_fin }}    @endif</td>
                        <td width="14%">@if($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie'] || in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) 00:00 @else {{ $cumul_prevu }} @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif(array_key_exists($date['dates'], $horaire_modif)) {{ $horaire_modif[$date['dates']]['matin_debut'] }} @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $matin_debut }} @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif(array_key_exists($date['dates'], $horaire_modif)) {{ $horaire_modif[$date['dates']]['matin_fin'] }} @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $matin_fin }}   @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif(array_key_exists($date['dates'], $horaire_modif)) {{ $horaire_modif[$date['dates']]['soir_debut'] }} @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $soir_debut }}  @endif</td>
                        <td width="7%">@if(in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) @elseif(array_key_exists($date['dates'], $horaire_modif)) {{ $horaire_modif[$date['dates']]['soir_fin'] }} @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie']) 00:00 @else {{ $soir_fin }}    @endif</td>
                        <td width="14%">@if(array_key_exists($date['dates'], $horaire_modif)) {{ $horaire_modif[$date['dates']]['cumul'] }} @elseif($date['disabled'] || $date['j_paye'] || $date['j_ferie'] || $date['j_maladie'] || in_array(DateTime::createFromFormat('d/m/Y', $date['dates'])->format('l'), ['Saturday', 'Sunday'])) 00:00 @else {{ $cumul_prevu }} @endif</td>
                    </tr>
            @endforeach
                <tr>
                    <td colspan="5" class="cumulSemaine indentCumul1"><b>Cumul Semaine {{ $counter + 1 }}</b></td>
                    <td class="cumulHoraire"><b>{{ $cumulPrevu[$counter] }}:00</b></td>
                    <td colspan="4" class="cumulSemaine indentCumul2"><b>Cumul Semaine {{ $counter + 1 }}</b></td>
                    <td class="cumulHoraire"><b>{{ $cumulRealise[$counter] }}:00</b></td>
                </tr>
            </table>
        @endforeach

        <table class="bottomTable">
            <tr>
                <td width="16%" class="text-start normalweight signature"><i>Signature salarié</i></td>
                <td width="7%" class="normalweight" style="background-color: #ffff00;">CP</td>
                <td width="7%" class="normalweight" style="background-color: #ffb7f2;">FERIE</td>
                <td width="14%" class="specialBottom fontSmaller">Cumul Mois Prévu</td>
                <td width="14%" class="specialBottom">{{ $totalPrevu }}:00</td>
                <td width="14%"></td>
                <td width="14%" class="specialBottom fontSmaller">Cumul Mois Réalisé</td>
                <td width="14%" class="specialBottom">{{ $totalRealise }}:00</td>
            </tr>
            <tr>
                <td></td>
                <td width="7%" class="normalweight" style="background-color: #00b050;">MALADIE</td>
                <td width="7%" class="normalweight" style="background-color: #86b9ff;">COURS</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td width="14%" class="specialBottom fontSmallerSmaller">Ecart (Réalisé-Prévu)</td>
                <td width="14%" class="specialBottom"><img src="{{ asset('arrow.svg') }}">{{ $totalDiff }}:00</td>
            </tr>
        </table>
        <div class="signatureManu">
            <img src="{{ $signature }}">
        </div>
    </div>
</div>

@if($download == "false")
    <style>
        .pdf_container
        {
            font-family: Verdana, serif;
            position: absolute;
            width: 90%;
            height: 93%;
            padding-bottom: 20px;
            margin-top: 10px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            color: #124556;
            border-right: 1px solid #d2d2d2;
            border-left: 1px solid #d2d2d2;
            border-bottom: 2px solid #d2d2d2;
        }

        .titleZindex
        {
            padding-right: 30px;
            width: 55%;
            position: relative;
        }

        .titlePdf
        {
            font-size: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #124556;
        }

        .datePdf
        {
            position: absolute;
            top: 130%;
            font-size: 8px;
            width: 30%;
            padding-bottom: 3px;
            border-bottom: 2px solid #ea1356;
            text-align: center;
        }

        .identitePdf
        {
            top: 130%;
            position: absolute;
            font-size: 8px;
            width: 80%;
            text-align: center;
            right: 10%;
            text-align: right;
        }

        .zindexLogo
        {
            position: absolute;
            top: 0;
            width: 24%;
            right: 0;
        }

        .zindexLogo img
        {
            width: 100%;
        }

        .top-table
        {
            width: 100%;
            margin-top: 6%;
            font-size: 7px;
            font-weight: bold;
            border-collapse: collapse;
        }

        .top-table td
        {
            color: #124556;
            text-align: center;
            border: 2px solid #ea1356;
        }

        .temps-table
        {
            margin-top: 2%;
            width: 100%;
            font-size: 7px;
            border-collapse: collapse;
            border-top: 2px solid #ea1356;
            border-right: 2px solid #ea1356;
        }

        .temps-table td
        {
            color: #124556;
            text-align: center;
            border: 1px solid #ea1356;
            height: 13.4px;
        }

        .cumulSemaine
        {
            text-align: end !important;
            padding-right: 10px !important;
            border-left: 0px solid transparent !important;
            border-bottom: 1px solid #ea1356 !important;
        }

        .cumulHoraire
        {
            border-bottom: 1px solid #ea1356 !important;
        }

        .paddingPdf
        {
            padding: 2%;

        }

        .bottomTable
        {
            width: 100%;
            margin-top: 2%;
            font-size: 7px;
            border-collapse: collapse;
            color: #124556;
            text-align: center;
        }

        .text-start
        {
            text-align: start;

        }

        .fontSmaller
        {
            font-size: 6px;
        }

        .fontSmallerSmaller
        {
            font-size: 5px;
        }

        .specialBottom
        {
            background-color: #e0e0e0;
            border: 1px solid #4c4c4c;
            border-bottom: 2px solid #4c4c4c;
            font-weight: bold;
            position: relative;
        }

        .specialBottom img
        {
            width: 8px;
            position: absolute;
            left: 5%;
            top: 2px;
        }

        .signatureManu
        {
            position: absolute;
            bottom: 15px;
            left: 2px;
            width: 16%;
            height: 5%;
        }

        .signatureManu img
        {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
@else
    <style>
        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        body{
            font-family: "source_sans_proregular", Calibri,Candara,Segoe,Segoe UI,Optima,Arial,sans-serif;
        }

        .pdf_container
        {
            position: absolute;
            width: 98%;
            height: 98%;
            padding-bottom: 40px;
            margin-top: -7px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            color: #124556;
            border-right: 2px solid #d2d2d2;
            border-left: 2px solid #d2d2d2;
            border-bottom: 3px solid #d2d2d2;
        }

        .titleZindex
        {
            padding-right: 30px;
            width: 60%;
            position: relative;
        }

        .titlePdf
        {
            font-size: 16px;
            padding-bottom: 5px;
            border-bottom: 2px solid #124556;
            font-weight: bold;
        }

        .datePdf
        {
            margin-top: 8px;
            font-size: 13px;
            width: 25%;
            padding-bottom: 3px;
            border-bottom: 3px solid #ea1356;
            text-align: center;
        }

        .identitePdf
        {
            font-size: 13px;
            width: 98%;
            text-align: center;
            right: 10%;
            text-align: right;
            margin-top: -30px;
        }

        .zindexLogo
        {
            position: absolute;
            top: 2%;
            width: 25%;
            right: 2%;
        }

        .zindexLogo img
        {
            width: 100%;
        }

        .top-table
        {
            width: 100%;
            margin-top: 3%;
            font-size: 11px;
            font-weight: bold;
            border-collapse: collapse;
        }

        .top-table td
        {
            color: #124556;
            text-align: center;
            border: 2px solid #ea1356;
        }

        .temps-table
        {
            margin-top: 2%;
            width: 100%;
            font-size: 11px;
            font-weight: normal;
            border-collapse: collapse;
            border-top: 2px solid #ea1356;
            border-right: 2px solid #ea1356;
        }

        .temps-table td
        {
            color: #124556;
            text-align: center;
            border: 1px solid #ea1356;
            height: 15px;
        }

        .cumulSemaine
        {
            padding-right: 10px !important;
            border-left: 0px solid transparent !important;
            border-bottom: 1px solid #ea1356 !important;
            height: 20px !important;
        }

        .indentCumul1
        {
            text-indent: 165px;
        }

        .indentCumul2
        {
            text-indent: 73px;
        }

        .cumulHoraire
        {
            border-bottom: 1px solid #ea1356 !important;
        }

        .paddingPdf
        {
            padding: 2%;

        }

        .bottomTable
        {
            width: 100%;
            margin-top: 2%;
            font-size: 11px;
            border-collapse: collapse;
            color: #124556;
            text-align: center;
        }

        .bottomTable td
        {
            height: 16px;
        }

        .text-start
        {
            text-align: start;

        }

        .fontSmaller
        {
            font-size: 9px;
        }

        .fontSmallerSmaller
        {
            font-size: 8px;
        }

        .specialBottom
        {
            background-color: #e0e0e0;
            border: 1px solid #4c4c4c;
            border-bottom: 2px solid #4c4c4c;
            font-weight: bold;
            position: relative;
        }

        .specialBottom img
        {
            width: 8px;
            position: absolute;
            left: 5%;
            top: 2px;
        }

        .normalweight
        {
            font-weight: normal;
        }

        .signature
        {
            text-indent: -25px;
        }

        .signatureManu
        {
            position: absolute;
            bottom: 2px;
            left: 2px;
            width: 16%;
            height: 5%;
        }

        .signatureManu img
        {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
@endif
