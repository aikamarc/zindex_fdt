let jour_modifie = [];
let counter = 0;

let horaire_modif = [];
let horaire_counter = 0;

var signaturePad;

$(document).ready(function() {

    $('.select2').select2({ width: "100%", closeOnSelect: true });

    getDateDebutFin();

    jour_modifie['paye']    = [];
    jour_modifie['ferie']   = [];
    jour_modifie['maladie'] = [];
    jour_modifie['cours']   = [];

    var canvas = document.getElementById('signature-pad');
    signaturePad = new SignaturePad(canvas);
});

function getDateDebutFin()
{
    $.ajax({
        url: '/getDateDebutFin',
        type: 'POST',
        headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  },
        data:
        {
            mois  : $('#month').val(),
            annee : $('#year').val(),
        },
    })
    .done(function(response) {

        $('#select_conge_paye').html('');
        $('#select_ferie').html('');
        $('#select_maladie').html('');
        $('#select_cours').html('');
        $('#select_horaire_modif').html('');

        Array.from( response.dates ).forEach(function(element) {
            let option_conge         = `<option value="${element}">${element}</option>`
            let option_maladie       = `<option value="${element}">${element}</option>`
            let option_ferie         = `<option value="${element}">${element}</option>`
            let option_cours         = `<option value="${element}">${element}</option>`
            let option_horaire_modif = `<option value="${element}">${element}</option>`

            $('#select_conge_paye').append(option_conge);
            $('#select_ferie').append(option_maladie);
            $('#select_maladie').append(option_ferie);
            $('#select_cours').append(option_cours);
            $('#select_horaire_modif').append(option_cours);
        });

        let option_conge         = `<option selected value="null" disabled>-- Ajouter une date --</option>`
        let option_maladie       = `<option selected value="null" disabled>-- Ajouter une date --</option>`
        let option_ferie         = `<option selected value="null" disabled>-- Ajouter une date --</option>`
        let option_cours         = `<option selected value="null" disabled>-- Ajouter une date --</option>`
        let option_horaire_modif = `<option selected value="null" disabled>-- Ajouter une date --</option>`

        $('#select_conge_paye').prepend(option_conge);
        $('#select_ferie').prepend(option_maladie);
        $('#select_maladie').prepend(option_ferie);
        $('#select_cours').prepend(option_cours);
        $('#select_horaire_modif').prepend(option_cours);


        $('.select2refresh').select2({ width: "100%", closeOnSelect: true });

        generatePdf()
    });
}

function updateHoraireDisplay(type)
{
    $(`#cadre-horaire-${type}`).html('');

    if(type != "AUCUN" && type != "ADD1H" && type != "REM1H")
    {
        Array.from( jour_modifie[type] ).forEach(function(element, index) {
            if(element != undefined )
            {
                $(`#cadre-horaire-${type}`).append(`<div onclick="removeThisDate('${type}', '${index}')">${element}</div>`)
            }
        });
    }

    $(`#cadre-horaire-${type}`).append();

    generatePdf();
}

function removeThisDate(type, index)
{
    jour_modifie[type].splice(index, 1);
    updateHoraireDisplay(type)
}

function add_horaire_modif()
{
    if( $('#select_horaire_modif').val()             == null ) { return; }
    if( $('#select_horaire_modif_matin_debut').val() == null ) { return; }
    if( $('#select_horaire_modif_matin_fin').val()   == null ) { return; }
    if( $('#select_horaire_modif_soir_debut').val()  == null ) { return; }
    if( $('#select_horaire_modif_soir_fin').val()    == null ) { return; }

    horaire_modif[horaire_counter] = [];
    horaire_modif[horaire_counter]['day']         = $('#select_horaire_modif').val();
    horaire_modif[horaire_counter]['matin_debut'] = $('#select_horaire_modif_matin_debut').val();
    horaire_modif[horaire_counter]['matin_fin']   = $('#select_horaire_modif_matin_fin').val();
    horaire_modif[horaire_counter]['soir_debut']  = $('#select_horaire_modif_soir_debut').val();
    horaire_modif[horaire_counter]['soir_fin']    = $('#select_horaire_modif_soir_fin').val();

    // $('#select_horaire_modif').val("null").trigger("change");
    // $('#select_horaire_modif_matin_debut').val("null").trigger("change");
    // $('#select_horaire_modif_matin_fin').val("null").trigger("change");
    // $('#select_horaire_modif_soir_debut').val("null").trigger("change");
    // $('#select_horaire_modif_soir_fin').val("null").trigger("change");

    horaire_counter++;

    updateHoraireModifierDisplay()
    generatePdf()
}

function updateHoraireModifierDisplay()
{
    $(`#cadre-horaire-modif`).html('');

    Array.from( horaire_modif ).forEach(function(element, index) {
        if(element != undefined )
        {
            $(`#cadre-horaire-modif`).append(`<div onclick="removeDate('${index}')"><div>${element['day']}</div><div>De ${element['matin_debut']} à ${element['matin_fin']} De ${element['soir_debut']} à ${element['soir_fin']}</div></div>`)
        }
        else
        {
            horaire_modif.splice(index, 1);
        }
    })

    if(horaire_modif.length > 0)
    {
        $(`#cadre-horaire-modif`).removeClass("disNone");
    }
    else
    {
        $(`#cadre-horaire-modif`).addClass("disNone");
    }

    generatePdf()
}

function removeDate(index)
{
    horaire_modif.splice(index, 1);
    updateHoraireModifierDisplay()
}

function saveSignature() {
    var dataURL = signaturePad.toDataURL();
    console.log(dataURL);
}

function generatePdf(returnPdf = false)
{
    setTimeout(function () {
        let formData = new FormData();
        formData.append('base_horaire_matin_debut', $('#horaire_matin_debut').val());
        formData.append('base_horaire_matin_fin'  , $('#horaire_matin_fin').val());
        formData.append('base_horaire_soir_debut' , $('#horaire_soir_debut').val());
        formData.append('base_horaire_soir_fin'   , $('#horaire_soir_fin').val());
        formData.append('j_paye'                  , jour_modifie['paye']);
        formData.append('j_ferie'                 , jour_modifie['ferie']);
        formData.append('j_maladie'               , jour_modifie['maladie']);
        formData.append('j_cours'                 , jour_modifie['cours']);
        formData.append('p_nom'                   , $('#input_nom').val());
        formData.append('p_prenom'                , $('#input_prenom').val());
        formData.append('month'                   , $('#month').val());
        formData.append('year'                    , $('#year').val());
        formData.append('returnPdf'               , returnPdf);

        if(returnPdf == true)
        {
            formData.append('signature', signaturePad.toDataURL());
        }
        else
        {
            formData.append('signature', generateSignature());
        }


        Array.from( horaire_modif ).forEach(function(element, index) {
            formData.append('horaire_modif_' + index,  JSON.stringify(Object.assign({}, element)));
        });

        $.ajax({
            url: '/generatePDF',
            type: 'POST',
            cache: false,
            contentType: false,
            processData:false,
            headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  },
            data: formData,
        })
        .done(function(response) {
            if(returnPdf)
            {
                window.open(response, '_blank');

                setTimeout(function () {
                    $.ajax({
                        url: '/removeFile',
                        type: 'POST',
                        headers: {  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  },
                        data: { response },
                    })
                }, 2500)
            }
            else
            {
                $('#pdf-container').html(response);
            }
        });
    }, 250)
}

function clearSignature()
{
    signaturePad.clear();
}

function generateSignature()
{
    let dataURL = signaturePad.toDataURL();
    let blob = dataURLToBlob(dataURL);
    let url = URL.createObjectURL(blob);

    return url;
}

function dataURLToBlob(dataURL) {
    var binary = atob(dataURL.split(',')[1]);
    var array = [];
    for (var i = 0; i < binary.length; i++) {
        array.push(binary.charCodeAt(i));
    }
    return new Blob([new Uint8Array(array)], { type: 'image/png' });
}

function updateSpecialDaySelected(element, type)
{
    $('.specialDayItemSelected').removeClass('specialDayItemSelected');
    $(element).addClass('specialDayItemSelected');
    $('#current_special_day').val(type);
}

function addJourArray(element, type)
{
    if($(element).val() != null)
    {
        let value = $(element).val();
        $(element).val("null").trigger("change");

        let find = false;

        Array.from( jour_modifie[type] ).forEach(function(item, index) {
            if(item == value)
            {
                find = true;
            }
        });

        if(find == false)
        {
            jour_modifie[type][counter] = value;
            counter++;
        }
    }
    updateHoraireDisplay(type);
    generatePdf()
}

function addInteractiveDay(date, element=null)
{
    if($('#current_special_day').val() == "AUCUN")
    {
        jour_modifie['cours'][jour_modifie['cours'].indexOf(date)] = null;
        jour_modifie['ferie'][jour_modifie['ferie'].indexOf(date)] = null;
        jour_modifie['maladie'][jour_modifie['maladie'].indexOf(date)] = null;
        jour_modifie['paye'][jour_modifie['paye'].indexOf(date)] = null;
    }
    else if($('#current_special_day').val() == "ADD1H" || $('#current_special_day').val() == "REM1H")
    {
        if(element != null) {

            let date0 = $($(element).parent().find('.hoverTdPdf')[0]).html().replaceAll(' ', '');
            let date1 = $($(element).parent().find('.hoverTdPdf')[1]).html().replaceAll(' ', '');
            let date2 = $($(element).parent().find('.hoverTdPdf')[2]).html().replaceAll(' ', '');
            let date3 = $($(element).parent().find('.hoverTdPdf')[3]).html().replaceAll(' ', '');

            if($($(element).parent().find('.hoverTdPdf')[0]).attr('id') == $(element).attr('id'))
            {
                let [hours, minutes] = date0.split(":").map(Number);
                hours += 1;
                if($('#current_special_day').val() == "REM1H") { hours -= 2; }
                if (hours >= 24) { hours -= 24; }
                if (hours < 0)   { hours += 24; }
                let formattedHours = String(hours).padStart(2, "0");
                let formattedMinutes = String(minutes).padStart(2, "0");
                date0 = `${formattedHours}:${formattedMinutes}`;
            }
            if($($(element).parent().find('.hoverTdPdf')[1]).attr('id') == $(element).attr('id'))
            {
                let [hours, minutes] = date1.split(":").map(Number);
                hours += 1;
                if($('#current_special_day').val() == "REM1H") { hours -= 2; }
                if (hours >= 24) { hours -= 24; }
                if (hours < 0)   { hours += 24; }
                let formattedHours = String(hours).padStart(2, "0");
                let formattedMinutes = String(minutes).padStart(2, "0");
                date1 = `${formattedHours}:${formattedMinutes}`;
            }
            if($($(element).parent().find('.hoverTdPdf')[2]).attr('id') == $(element).attr('id'))
            {
                let [hours, minutes] = date2.split(":").map(Number);
                hours += 1;
                if($('#current_special_day').val() == "REM1H") { hours -= 2; }
                if (hours >= 24) { hours -= 24; }
                if (hours < 0)   { hours += 24; }
                let formattedHours = String(hours).padStart(2, "0");
                let formattedMinutes = String(minutes).padStart(2, "0");
                date2 = `${formattedHours}:${formattedMinutes}`;
            }
            if($($(element).parent().find('.hoverTdPdf')[3]).attr('id') == $(element).attr('id'))
            {
                let [hours, minutes] = date3.split(":").map(Number);
                hours += 1;
                if($('#current_special_day').val() == "REM1H") { hours -= 2; }
                if (hours >= 24) { hours -= 24; }
                if (hours < 0)   { hours += 24; }
                let formattedHours = String(hours).padStart(2, "0");
                let formattedMinutes = String(minutes).padStart(2, "0");
                date3 = `${formattedHours}:${formattedMinutes}`;
            }

            $('#select_horaire_modif').val(date).trigger('change');
            $('#select_horaire_modif_matin_debut').val(date0).trigger('change');
            $('#select_horaire_modif_matin_fin').val(date1).trigger('change');
            $('#select_horaire_modif_soir_debut').val(date2).trigger('change');
            $('#select_horaire_modif_soir_fin').val(date3).trigger('change');

            add_horaire_modif();
        }

    }
    else if($('#current_special_day').val() != null)
    {
        let value = date;
        let find = false;

        Array.from( jour_modifie[$('#current_special_day').val()] ).forEach(function(item, index) {
            if(item == value)
            {
                find = true;
            }
        });

        if(find == false)
        {
            jour_modifie[$('#current_special_day').val()][counter] = value;
            counter++;
        }
    }

    updateHoraireDisplay($('#current_special_day').val());
    generatePdf()
}
