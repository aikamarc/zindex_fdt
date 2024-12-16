<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function home()
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $view_params = [];
        $view_params['horaires'] = [
            '00:00', '00:15', '00:30', '00:45',
            '01:00', '01:15', '01:30', '01:45',
            '02:00', '02:15', '02:30', '02:45',
            '03:00', '03:15', '03:30', '03:45',
            '04:00', '04:15', '04:30', '04:45',
            '05:00', '05:15', '05:30', '05:45',
            '06:00', '06:15', '06:30', '06:45',
            '07:00', '07:15', '07:30', '07:45',
            '08:00', '08:15', '08:30', '08:45',
            '09:00', '09:15', '09:30', '09:45',
            '10:00', '10:15', '10:30', '10:45',
            '11:00', '11:15', '11:30', '11:45',
            '12:00', '12:15', '12:30', '12:45',
            '13:00', '13:15', '13:30', '13:45',
            '14:00', '14:15', '14:30', '14:45',
            '15:00', '15:15', '15:30', '15:45',
            '16:00', '16:15', '16:30', '16:45',
            '17:00', '17:15', '17:30', '17:45',
            '18:00', '18:15', '18:30', '18:45',
            '19:00', '19:15', '19:30', '19:45',
            '20:00', '20:15', '20:30', '20:45',
            '21:00', '21:15', '21:30', '21:45',
            '22:00', '22:15', '22:30', '22:45',
            '23:00', '23:15', '23:30', '23:45'
        ];

        return view('home', $view_params);
    }

    /**
     * "mois" => "05"
     * "annee" => "2024"
     * "day" => "default"
     */
    public function getDateDebutFin(Request $request)
    {
        $dateDebutMois = \Carbon\Carbon::create($request->annee, $request->mois, 1);

        if ($request->day !== 'default') {
            $jour = (int)$request->day;
            if ($jour >= 1 && $jour <= $dateDebutMois->daysInMonth) {
                $dateDebutMois->day = $jour;
            } else {
                return response()->json(['error' => 'Invalid day value'], 400);
            }
        } else {
            if ($dateDebutMois->dayOfWeek !== \Carbon\Carbon::MONDAY) {
                $dateDebutMois->modify('last monday');
            }
        }

        $dateFinMois = (clone $dateDebutMois)->addWeeks(5)->subDay();

        if ($dateFinMois->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
            $dateFinMois->modify('next sunday');
        }

        $dates = [];
        $currentDate = $dateDebutMois->copy();
        while ($currentDate <= $dateFinMois) {
            $dates[] = $currentDate->format('d/m/Y');
            $currentDate->addDay();
        }

        return response()->json(['dates' => $dates]);
    }

    public function getMois($month)
    {
        switch($month)
        {
            case '01': return "janvier";
            case '02': return "février";
            case '03': return "mars";
            case '04': return "avril";
            case '05': return "mai";
            case '06': return "juin";
            case '07': return "juillet";
            case '08': return "août";
            case '09': return "septembre";
            case '10': return "octobre";
            case '11': return "novembre";
            case '12': return "décembre";
            default:   return "";
        }
    }

    public function calculerDureeTotale($matin_debut, $matin_fin, $soir_debut, $soir_fin) {
        $matinDebut = new DateTime($matin_debut);
        $matinFin = new DateTime($matin_fin);
        $soirDebut = new DateTime($soir_debut);
        $soirFin = new DateTime($soir_fin);

        $intervalleMatin = $matinDebut->diff($matinFin);
        $intervalleSoir = $soirDebut->diff($soirFin);

        $minutesTotales = ($intervalleMatin->h * 60 + $intervalleMatin->i) + ($intervalleSoir->h * 60 + $intervalleSoir->i);

        $heures = intdiv($minutesTotales, 60);
        $minutes = $minutesTotales % 60;

        return sprintf("%02d:%02d", $heures, $minutes);
    }

    function addTimes($times) {
        $totalMinutes = 0;

        foreach ($times as $time) {
            list($hours, $minutes) = explode(':', $time);
            $totalMinutes += $hours * 60 + $minutes;
        }

        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        $totalHours = str_pad($totalHours, 2, '0', STR_PAD_LEFT);
        $remainingMinutes = str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT);

        return $totalHours . ':' . $remainingMinutes;
    }

    function calculerTotalHeures($cumulArray) {
        $totalMinutes = 0;

        foreach ($cumulArray as $time) {
            list($hours, $minutes) = explode(':', $time);
            $totalMinutes += $hours * 60 + $minutes;
        }

        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        return sprintf("%02d:%02d", $totalHours, $remainingMinutes);
    }

    function timeDifference($time1, $time2) {
        list($hours1, $minutes1) = explode(':', $time1); $totalMinutes1 = ($hours1 * 60) + $minutes1;
        list($hours2, $minutes2) = explode(':', $time2); $totalMinutes2 = ($hours2 * 60) + $minutes2;

        $differenceInMinutes = abs($totalMinutes2 - $totalMinutes1);

        $hoursDifference = floor($differenceInMinutes / 60);
        $minutesDifference = $differenceInMinutes % 60;

        $difference = sprintf('%02d:%02d', $hoursDifference, $minutesDifference);

        return $difference;
    }

    /*
        "response" => "http://fdt.test/1717899790_FDT.pdf"
    */
    public function removeFile(Request $request)
    {
        $fileName = basename($request->response);
        $filePath = __DIR__ . "/../../../public/" . $fileName;

        if (file_exists($filePath))
        {
            unlink($filePath);
            unlink(str_replace('.pdf', '.png', $filePath));
        }

        return response()->json(['success' => true, 'message' => 'File deleted successfully']);
    }

    public function generatePDF(Request $request)
    {
        // Création de la date de début en fonction de l'année et du mois
        $dateDebutMois = \Carbon\Carbon::create($request->year, $request->month, 1);

        // Si le jour est différent de "default", ajuster la date de début
        if ($request->day !== 'default') {
            // Vérifie que $request->day est un jour valide
            $jour = (int)$request->day;
            if ($jour >= 1 && $jour <= $dateDebutMois->daysInMonth) {
                $dateDebutMois->day = $jour;
            } else {
                return response()->json(['error' => 'Invalid day value'], 400);
            }
        } else {
            // Ajuster au dernier lundi si le jour est "default"
            if ($dateDebutMois->dayOfWeek !== \Carbon\Carbon::MONDAY) {
                $dateDebutMois->modify('last monday');
            }
        }

        // Calcul de la date de fin
        $dateFinMois = (clone $dateDebutMois)->addWeeks(5)->subDay(); // Inclure 5 semaines depuis la date de début

        // Ajuster la date de fin au prochain dimanche si nécessaire
        if ($dateFinMois->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
            $dateFinMois->modify('next sunday');
        }

        // Génération des dates
        $dates = [];
        $currentDate = $dateDebutMois->copy();

        while ($currentDate <= $dateFinMois) {
            $dates[] = [
                "dates"     => $currentDate->format('d/m/Y'),
                "disabled"  => $currentDate->format('m') != $request->month,
                "j_paye"    => in_array($currentDate->format('d/m/Y'), explode(",", $request->j_paye) ?? []),
                "j_matin"   => in_array($currentDate->format('d/m/Y'), explode(",", $request->j_matin) ?? []),
                "j_aprem"   => in_array($currentDate->format('d/m/Y'), explode(",", $request->j_aprem) ?? []),
                "j_ferie"   => in_array($currentDate->format('d/m/Y'), explode(",", $request->j_ferie) ?? []),
                "j_maladie" => in_array($currentDate->format('d/m/Y'), explode(",", $request->j_maladie) ?? []),
                "j_cours"   => in_array($currentDate->format('d/m/Y'), explode(",", $request->j_cours) ?? []),
            ];
            $currentDate->addDay();
        }

        $allDates = array_chunk($dates, 7);
        if(count($allDates) == 6) { unset($allDates[5]); }

        $horaire_modif = [];

        foreach($request->all() as $key => $value)
        {
            if( str_starts_with($key, 'horaire_modif_'))
            {
                $json = json_decode($value);

                $horaire_modif[$json->day] = [
                    "day"         => $json->day,
                    "matin_debut" => $json->matin_debut,
                    "matin_fin"   => $json->matin_fin,
                    "soir_debut"  => $json->soir_debut,
                    "soir_fin"    => $json->soir_fin,
                    "cumul"       => $this->calculerDureeTotale($json->matin_debut, $json->matin_fin, $json->soir_debut, $json->soir_fin ),
                ];
            }
        }

        $cumulPrevu = [];
        foreach($allDates as $index => $semaine)
        {
            $cumulPrevu[$index] = "00:00";

            foreach($semaine as $c => $jour)
            {
                if($allDates[$index][$c]["j_matin"])    {
                    switch(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'))
                    {
                        case 'Monday':    $additionBaseHoraire_lundi[$index.$c]      = $this->calculerDureeTotale("0:00", "0:00", $request->base_lundi_horaire_soir_debut, $request->base_lundi_horaire_soir_fin);        break;
                        case 'Tuesday':   $additionBaseHoraire_mardi[$index.$c]      = $this->calculerDureeTotale("0:00", "0:00", $request->base_mardi_horaire_soir_debut, $request->base_mardi_horaire_soir_fin);        break;
                        case 'Wednesday': $additionBaseHoraire_mercredi[$index.$c]   = $this->calculerDureeTotale("0:00", "0:00", $request->base_mercredi_horaire_soir_debut, $request->base_mercredi_horaire_soir_fin);  break;
                        case 'Thursday':  $additionBaseHoraire_jeudi[$index.$c]      = $this->calculerDureeTotale("0:00", "0:00", $request->base_jeudi_horaire_soir_debut, $request->base_jeudi_horaire_soir_fin);        break;
                        case 'Friday':    $additionBaseHoraire_vendredi[$index.$c]   = $this->calculerDureeTotale("0:00", "0:00", $request->base_vendredi_horaire_soir_debut, $request->base_vendredi_horaire_soir_fin);  break;
                    }
                }
                elseif($allDates[$index][$c]["j_aprem"])    {
                    switch(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'))
                    {
                        case 'Monday':    $additionBaseHoraire_lundi[$index.$c]      = $this->calculerDureeTotale($request->base_lundi_horaire_matin_debut, $request->base_lundi_horaire_matin_fin ,"0:00", "0:00");       break;
                        case 'Tuesday':   $additionBaseHoraire_mardi[$index.$c]      = $this->calculerDureeTotale($request->base_mardi_horaire_matin_debut, $request->base_mardi_horaire_matin_fin, "0:00", "0:00");       break;
                        case 'Wednesday': $additionBaseHoraire_mercredi[$index.$c]   = $this->calculerDureeTotale($request->base_mercredi_horaire_matin_debut, $request->base_mercredi_horaire_matin_fin, "0:00", "0:00"); break;
                        case 'Thursday':  $additionBaseHoraire_jeudi[$index.$c]      = $this->calculerDureeTotale($request->base_jeudi_horaire_matin_debut, $request->base_jeudi_horaire_matin_fin, "0:00", "0:00");       break;
                        case 'Friday':    $additionBaseHoraire_vendredi[$index.$c]   = $this->calculerDureeTotale($request->base_vendredi_horaire_matin_debut, $request->base_vendredi_horaire_matin_fin, "0:00", "0:00"); break;
                    }
                }
                else {
                    $additionBaseHoraire_lundi[$index.$c]      = $this->calculerDureeTotale($request->base_lundi_horaire_matin_debut, $request->base_lundi_horaire_matin_fin, $request->base_lundi_horaire_soir_debut, $request->base_lundi_horaire_soir_fin);
                    $additionBaseHoraire_mardi[$index.$c]      = $this->calculerDureeTotale($request->base_mardi_horaire_matin_debut, $request->base_mardi_horaire_matin_fin, $request->base_mardi_horaire_soir_debut, $request->base_mardi_horaire_soir_fin);
                    $additionBaseHoraire_mercredi[$index.$c]   = $this->calculerDureeTotale($request->base_mercredi_horaire_matin_debut, $request->base_mercredi_horaire_matin_fin, $request->base_mercredi_horaire_soir_debut, $request->base_mercredi_horaire_soir_fin);
                    $additionBaseHoraire_jeudi[$index.$c]      = $this->calculerDureeTotale($request->base_jeudi_horaire_matin_debut, $request->base_jeudi_horaire_matin_fin, $request->base_jeudi_horaire_soir_debut, $request->base_jeudi_horaire_soir_fin);
                    $additionBaseHoraire_vendredi[$index.$c]   = $this->calculerDureeTotale($request->base_vendredi_horaire_matin_debut, $request->base_vendredi_horaire_matin_fin, $request->base_vendredi_horaire_soir_debut, $request->base_vendredi_horaire_soir_fin);
                }

                if(explode('/',$jour['dates'])[1] != $request->month) { /* Ne rien faire */ }
                elseif(in_array(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'), ['Saturday', 'Sunday'])) { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_paye"])     { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_ferie"])    { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_maladie"])  { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_cours"])    { /* Ne rien faire */ }
                else
                {
                    switch(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'))
                    {
                        case 'Monday':    $cumulPrevu[$index] = $this->addTimes([$cumulPrevu[$index], $additionBaseHoraire_lundi[$index.$c]]);    break;
                        case 'Tuesday':   $cumulPrevu[$index] = $this->addTimes([$cumulPrevu[$index], $additionBaseHoraire_mardi[$index.$c]]);    break;
                        case 'Wednesday': $cumulPrevu[$index] = $this->addTimes([$cumulPrevu[$index], $additionBaseHoraire_mercredi[$index.$c]]); break;
                        case 'Thursday':  $cumulPrevu[$index] = $this->addTimes([$cumulPrevu[$index], $additionBaseHoraire_jeudi[$index.$c]]);    break;
                        case 'Friday':    $cumulPrevu[$index] = $this->addTimes([$cumulPrevu[$index], $additionBaseHoraire_vendredi[$index.$c]]); break;
                    }
                }
            }
        }

        $cumulRealise = [];

        foreach($allDates as $index => $semaine)
        {
            $cumulRealise[$index] = "00:00";

            foreach($semaine as $c => $jour)
            {
                if($allDates[$index][$c]["j_matin"])    {
                    switch(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'))
                    {
                        case 'Monday':    $additionBaseHoraire_lundi[$index.$c]      = $this->calculerDureeTotale("0:00", "0:00", $request->base_lundi_horaire_soir_debut, $request->base_lundi_horaire_soir_fin);        break;
                        case 'Tuesday':   $additionBaseHoraire_mardi[$index.$c]      = $this->calculerDureeTotale("0:00", "0:00", $request->base_mardi_horaire_soir_debut, $request->base_mardi_horaire_soir_fin);        break;
                        case 'Wednesday': $additionBaseHoraire_mercredi[$index.$c]   = $this->calculerDureeTotale("0:00", "0:00", $request->base_mercredi_horaire_soir_debut, $request->base_mercredi_horaire_soir_fin);  break;
                        case 'Thursday':  $additionBaseHoraire_jeudi[$index.$c]      = $this->calculerDureeTotale("0:00", "0:00", $request->base_jeudi_horaire_soir_debut, $request->base_jeudi_horaire_soir_fin);        break;
                        case 'Friday':    $additionBaseHoraire_vendredi[$index.$c]   = $this->calculerDureeTotale("0:00", "0:00", $request->base_vendredi_horaire_soir_debut, $request->base_vendredi_horaire_soir_fin);  break;
                    }
                }
                elseif($allDates[$index][$c]["j_aprem"])    {
                    switch(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'))
                    {
                        case 'Monday':    $additionBaseHoraire_lundi[$index.$c]      = $this->calculerDureeTotale($request->base_lundi_horaire_matin_debut, $request->base_lundi_horaire_matin_fin ,"0:00", "0:00");       break;
                        case 'Tuesday':   $additionBaseHoraire_mardi[$index.$c]      = $this->calculerDureeTotale($request->base_mardi_horaire_matin_debut, $request->base_mardi_horaire_matin_fin, "0:00", "0:00");       break;
                        case 'Wednesday': $additionBaseHoraire_mercredi[$index.$c]   = $this->calculerDureeTotale($request->base_mercredi_horaire_matin_debut, $request->base_mercredi_horaire_matin_fin, "0:00", "0:00"); break;
                        case 'Thursday':  $additionBaseHoraire_jeudi[$index.$c]      = $this->calculerDureeTotale($request->base_jeudi_horaire_matin_debut, $request->base_jeudi_horaire_matin_fin, "0:00", "0:00");       break;
                        case 'Friday':    $additionBaseHoraire_vendredi[$index.$c]   = $this->calculerDureeTotale($request->base_vendredi_horaire_matin_debut, $request->base_vendredi_horaire_matin_fin, "0:00", "0:00"); break;
                    }
                }
                else {
                    $additionBaseHoraire_lundi[$index.$c]      = $this->calculerDureeTotale($request->base_lundi_horaire_matin_debut, $request->base_lundi_horaire_matin_fin, $request->base_lundi_horaire_soir_debut, $request->base_lundi_horaire_soir_fin);
                    $additionBaseHoraire_mardi[$index.$c]      = $this->calculerDureeTotale($request->base_mardi_horaire_matin_debut, $request->base_mardi_horaire_matin_fin, $request->base_mardi_horaire_soir_debut, $request->base_mardi_horaire_soir_fin);
                    $additionBaseHoraire_mercredi[$index.$c]   = $this->calculerDureeTotale($request->base_mercredi_horaire_matin_debut, $request->base_mercredi_horaire_matin_fin, $request->base_mercredi_horaire_soir_debut, $request->base_mercredi_horaire_soir_fin);
                    $additionBaseHoraire_jeudi[$index.$c]      = $this->calculerDureeTotale($request->base_jeudi_horaire_matin_debut, $request->base_jeudi_horaire_matin_fin, $request->base_jeudi_horaire_soir_debut, $request->base_jeudi_horaire_soir_fin);
                    $additionBaseHoraire_vendredi[$index.$c]   = $this->calculerDureeTotale($request->base_vendredi_horaire_matin_debut, $request->base_vendredi_horaire_matin_fin, $request->base_vendredi_horaire_soir_debut, $request->base_vendredi_horaire_soir_fin);
                }

                if(array_key_exists($jour['dates'], $horaire_modif))
                {
                    $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $horaire_modif[$jour['dates']]['cumul']]);
                }
                elseif(explode('/',$jour['dates'])[1] != $request->month) { /* Ne rien faire */ }
                elseif(in_array(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'), ['Saturday', 'Sunday'])) { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_paye"])     { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_ferie"])    { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_maladie"])  { /* Ne rien faire */ }
                elseif($allDates[$index][$c]["j_cours"])    { /* Ne rien faire */ }
                else
                {
                    // $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $additionBaseHoraire]);
                    switch(DateTime::createFromFormat('d/m/Y', $jour['dates'])->format('l'))
                    {
                        case 'Monday':    $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $additionBaseHoraire_lundi[$index.$c]]);    break;
                        case 'Tuesday':   $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $additionBaseHoraire_mardi[$index.$c]]);    break;
                        case 'Wednesday': $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $additionBaseHoraire_mercredi[$index.$c]]); break;
                        case 'Thursday':  $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $additionBaseHoraire_jeudi[$index.$c]]);    break;
                        case 'Friday':    $cumulRealise[$index] = $this->addTimes([$cumulRealise[$index], $additionBaseHoraire_vendredi[$index.$c]]); break;
                    }
                }
            }
        }

        $totalPrevu = $this->calculerTotalHeures($cumulPrevu);
        $totalRealise = $this->calculerTotalHeures($cumulRealise);

        $signature = $request->signature;

        $isEditing = true;
        if($request->returnPdf == "true") { $isEditing = false; }

        $data = [
            'lundi_matin_debut'     => $request->base_lundi_horaire_matin_debut,
            'lundi_matin_fin'       => $request->base_lundi_horaire_matin_fin,
            'lundi_soir_debut'      => $request->base_lundi_horaire_soir_debut,
            'lundi_soir_fin'        => $request->base_lundi_horaire_soir_fin,
            'mardi_matin_debut'     => $request->base_mardi_horaire_matin_debut,
            'mardi_matin_fin'       => $request->base_mardi_horaire_matin_fin,
            'mardi_soir_debut'      => $request->base_mardi_horaire_soir_debut,
            'mardi_soir_fin'        => $request->base_mardi_horaire_soir_fin,
            'mercredi_matin_debut'  => $request->base_mercredi_horaire_matin_debut,
            'mercredi_matin_fin'    => $request->base_mercredi_horaire_matin_fin,
            'mercredi_soir_debut'   => $request->base_mercredi_horaire_soir_debut,
            'mercredi_soir_fin'     => $request->base_mercredi_horaire_soir_fin,
            'jeudi_matin_debut'     => $request->base_jeudi_horaire_matin_debut,
            'jeudi_matin_fin'       => $request->base_jeudi_horaire_matin_fin,
            'jeudi_soir_debut'      => $request->base_jeudi_horaire_soir_debut,
            'jeudi_soir_fin'        => $request->base_jeudi_horaire_soir_fin,
            'vendredi_matin_debut'  => $request->base_vendredi_horaire_matin_debut,
            'vendredi_matin_fin'    => $request->base_vendredi_horaire_matin_fin,
            'vendredi_soir_debut'   => $request->base_vendredi_horaire_soir_debut,
            'vendredi_soir_fin'     => $request->base_vendredi_horaire_soir_fin,
            'cumul_prevu_lundi'     => $additionBaseHoraire_lundi,
            'cumul_prevu_mardi'     => $additionBaseHoraire_mardi,
            'cumul_prevu_mercredi'  => $additionBaseHoraire_mercredi,
            'cumul_prevu_jeudi'     => $additionBaseHoraire_jeudi,
            'cumul_prevu_vendredi'  => $additionBaseHoraire_vendredi,
            'nom'                   => $request->p_nom,
            'prenom'                => $request->p_prenom,
            'dates'                 => $allDates,
            'mois'                  => $this->getMois($request->month),
            'annee'                 => $request->year,
            'horaire_modif'         => $horaire_modif,
            'cumulRealise'          => $cumulRealise,
            'cumulPrevu'            => $cumulPrevu,
            'totalPrevu'            => $totalPrevu,
            'totalRealise'          => $totalRealise,
            'totalDiff'             => $this->timeDifference($totalPrevu, $totalRealise),
            'download'              => $request->returnPdf,
            'signature'             => $signature,
            'isEditing'             => $isEditing,
        ];

        if($request->returnPdf == "true")
        {
            $filename = strtoupper($request->p_prenom) . "-" . strtoupper($request->p_nom) . "-" . strtoupper($this->removeAccents($this->getMois($request->month))) . "-" . strtoupper($request->year) . ".pdf";

            $pdf = Pdf::loadView('pdf', $data)->setPaper('a4', 'portrait');
            $pdf->save(public_path() . '/' . $filename);

            file_put_contents(public_path() . '/' . str_replace('.pdf', '.png', $filename), file_get_contents($signature));

            return response()->json(asset($filename));
        }
        else
        {
            return view('pdf', $data);
        }
    }

    function removeAccents($string)
    {
        $unwantedChars = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'TH', 'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y'
        ];

        return strtr($string, $unwantedChars);
    }
}
