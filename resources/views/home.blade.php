@extends('layouts.layout')

<div class="opt_top_container">
    <select class="select2refresh" id="select_conge_paye" onchange="addJourArray(this, 'paye')"></select>
    <select class="select2refresh" id="select_ferie"      onchange="addJourArray(this, 'ferie')"></select>
    <select class="select2refresh" id="select_maladie"    onchange="addJourArray(this, 'maladie')"></select>
    <select class="select2refresh" id="select_cours"      onchange="addJourArray(this, 'cours')"></select>
</div>

<div class="container-parent">

    <div class="container">
        <div class="specialDayContainer">
            <div onclick="updateSpecialDaySelected(this, 'CONGES')"  class="specialDayItem pastille-conge"><div>Congés</div></div>
            <div onclick="updateSpecialDaySelected(this, 'FERIE')"   class="specialDayItem pastille-ferie"><div>Ferié</div></div>
            <div onclick="updateSpecialDaySelected(this, 'MALADIE')" class="specialDayItem pastille-maladie"><div>Maladie</div></div>
            <div onclick="updateSpecialDaySelected(this, 'COURS')"   class="specialDayItem pastille-cours"><div>Cours</div></div>
            <div onclick="updateSpecialDaySelected(this, 'AUCUN')"   class="specialDayItem specialDayItemSelected pastille-empty"><div>Aucun</div></div>
        </div>
        <input type="hidden" id="current_special_day" value="AUCUN">
    </div>

    <div class="container">

        <select class="select2" name="month" id="month" onchange="getDateDebutFin()">
            <option @if(date('m') == "01") selected @endif value="01">Janvier</option>
            <option @if(date('m') == "02") selected @endif value="02">Février</option>
            <option @if(date('m') == "03") selected @endif value="03">Mars</option>
            <option @if(date('m') == "04") selected @endif value="04">Avril</option>
            <option @if(date('m') == "05") selected @endif value="05">Mai</option>
            <option @if(date('m') == "06") selected @endif value="06">Juin</option>
            <option @if(date('m') == "07") selected @endif value="07">Juillet</option>
            <option @if(date('m') == "08") selected @endif value="08">Août</option>
            <option @if(date('m') == "09") selected @endif value="09">Septembre</option>
            <option @if(date('m') == "10") selected @endif value="10">Octobre</option>
            <option @if(date('m') == "11") selected @endif value="11">Novembre</option>
            <option @if(date('m') == "12") selected @endif value="12">Décembre</option>
        </select>

        <select class="select2" name="year" id="year" onchange="getDateDebutFin()">
            <option value="{{ date("Y", strtotime("-1 year")) }}">{{ date("Y", strtotime("-1 year")) }}</option>
            <option value="{{ date("Y") }}" selected>{{ date("Y") }}</option>
            <option value="{{ date("Y", strtotime("+1 year")) }}">{{ date("Y", strtotime("+1 year")) }}</option>
        </select>

        <input type="text" id="input_nom" class="inputForm" placeholder="Nom..." name="nom" onchange="generatePdf()">

        <input type="text" id="input_prenom" class="inputForm" placeholder="Prénom..." name="prenom" onchange="generatePdf()">

        <select class="select2" onchange="generatePdf()" id="horaire_matin_debut">
            @foreach($horaires as $horaire)
                <option @if($horaire == "09:00") selected @endif value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2" onchange="generatePdf()" id="horaire_matin_fin">
            @foreach($horaires as $horaire)
                <option @if($horaire == "12:00") selected @endif value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2" onchange="generatePdf()" id="horaire_soir_debut">
            @foreach($horaires as $horaire)
                <option @if($horaire == "13:00") selected @endif value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2" onchange="generatePdf()" id="horaire_soir_fin">
            @foreach($horaires as $horaire)
                <option @if($horaire == "17:00") selected @endif value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2refresh" id="select_horaire_modif" onchange="add_horaire_modif()"></select>

        <select class="select2refresh" id="select_horaire_modif_matin_debut" onchange="add_horaire_modif()">
            <option disabled selected>Début</option>
            @foreach($horaires as $horaire)
                <option value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2refresh" id="select_horaire_modif_matin_fin" onchange="add_horaire_modif()">
            <option disabled selected>Fin</option>
            @foreach($horaires as $horaire)
                <option value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2refresh" id="select_horaire_modif_soir_debut" onchange="add_horaire_modif()">
            <option disabled selected>Début</option>
            @foreach($horaires as $horaire)
                <option value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <select class="select2refresh" id="select_horaire_modif_soir_fin" onchange="add_horaire_modif()">
            <option disabled selected>Fin</option>
            @foreach($horaires as $horaire)
                <option value="{{ $horaire }}">{{ $horaire }}</option>
            @endforeach
        </select>

        <div class="signatureContainer">
            <div class="signatureElementAction">
                <canvas id="signature-pad" width="400" height="200"></canvas>
                <button onclick="generatePdf()">✓</button>
                <button onclick="clearSignature()">✗</button>
            </div>
        </div>
    </div>

</div>

<div class="pdf-container">
    <div id="pdf-container"></div>
    <button onclick="generatePdf(true)">Télécharger</button>
</div>
