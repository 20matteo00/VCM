<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomstarter\Helpers\Competizione;

$user = Factory::getUser();
$userId = $user->id;

if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    if ($_GET['module_id'] != 117) {
        header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=117");
        exit;
    }
    $tableStatistiche = Competizione::getTableStatistiche($idcomp);
    $tablePartite = Competizione::getTablePartite($idcomp);
    $competizione = Competizione::getCompetizioneById($idcomp, $userId);
    $ar = $competizione->andata_ritorno;
    $mod = $competizione->modalita;
    if (isset($competizione->gironi)) {
        $gironi = $competizione->gironi;
    } else {
        $gironi = 1;
    }
    $checkgol = Competizione::checkGolNull($tablePartite);
    $gir = false;
    // Ottieni la classifica
    $classifica = Competizione::getClassifica(tableStatistiche: $tableStatistiche);
    $numsquadre = count($classifica);

    // Determina la vista
    $view = isset($_POST['Casa']) ? 'casa' :
        (isset($_POST['Trasferta']) ? 'trasferta' :
            (isset($_POST['Andata']) ? 'andata' :
                (isset($_POST['Ritorno']) ? 'ritorno' :
                    (isset($_POST['Andamento']) ? 'andamento' :
                        (isset($_POST['Gironi']) ? 'gironi' : 'totale')))));

    if ($view === 'andata' && $ar === 1) {
        $classifica = Competizione::getClassificaAR($tablePartite, $ar, $numsquadre, $view, $mod, $gironi);
    } elseif ($view === 'ritorno' && $ar === 1) {
        $classifica = Competizione::getClassificaAR($tablePartite, $ar, $numsquadre, $view, $mod, $gironi);
    } elseif ($view === 'andamento') {
        $classifica = NULL;
        $andamento = Competizione::getAndamento($tablePartite);
    } elseif ($view === 'gironi') {
        $classifica = NULL;
        $gir = true;
    }
    ?>
    <div class="container classifica mybar">
        <form method="post" action="">
            <div class="container p-2">
                <div class="row justify-content-between">
                    <input type="hidden" name="module_id" value="117">
                    <div class="col-6 col-md-4 col-lg-auto mb-2">
                        <button type="submit" name="Totale" class="btn btn-info w-100">Totale</button>
                    </div>
                    <div class="col-6 col-md-4 col-lg-auto mb-2">
                        <button type="submit" name="Andamento" class="btn btn-info w-100">Andamento</button>
                    </div>
                    <div class="col-6 col-md-4 col-lg-auto mb-2">
                        <button type="submit" name="Casa" class="btn btn-info w-100">Casa</button>
                    </div>
                    <div class="col-6 col-md-4 col-lg-auto mb-2">
                        <button type="submit" name="Trasferta" class="btn btn-info w-100">Trasferta</button>
                    </div>
                    <div class="col-6 col-md-4 col-lg-auto mb-2">
                        <button type="submit" name="Andata" class="btn btn-info w-100">Andata</button>
                    </div>
                    <div class="col-6 col-md-4 col-lg-auto mb-2">
                        <button type="submit" name="Ritorno" class="btn btn-info w-100">Ritorno</button>
                    </div>
                    <?php if ($mod === 70): ?>
                        <div class="col-6 col-md-4 col-lg-auto mb-2">
                            <button type="submit" name="Gironi" class="btn btn-info w-100">Gironi</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <?php if (!empty($classifica) && !$checkgol): ?>
            <div class="table-responsive my-5">
                <table class="table table-striped table-bordered text-center category-table">
                    <thead class="thead-dark">
                        <?php ($mod === 70) ? $colspan = 3 : $colspan = 2; ?>
                        <tr>
                            <td class="fw-bold" colspan="<?php echo $colspan; ?>">Rank</td>
                            <td class="fw-bold" colspan="8"><?php echo ucfirst($view); ?></td>
                        </tr>
                        <tr>
                            <th class="category-header-logo">#</th>
                            <?php if ($mod === 70)
                                echo '<th class="category-header-logo">Girone</th>'; ?>
                            <th class="category-header-logo">Squadra</th>
                            <th class="category-header-logo">Pt</th>
                            <th class="category-header-logo">G</th>
                            <th class="category-header-logo">V</th>
                            <th class="category-header-logo">N</th>
                            <th class="category-header-logo">P</th>
                            <th class="category-header-logo">GF</th>
                            <th class="category-header-logo">GS</th>
                            <th class="category-header-logo">DR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $posizione = 1;
                        foreach ($classifica as $squadra):
                            // Calcola le statistiche
                            $stats = Competizione::calculateStatistics($squadra, $view, $ar, $tablePartite);
                            if (isset($squadra->squadra)) {
                                $cf = Competizione::getCustomFields($squadra->squadra);
                            } else {
                                $cf = Competizione::getCustomFields($stats['squadra']);
                            }

                            // Retrieve color values with defaults
                            $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                            $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                

                            ?>
                            <tr>
                                <td class="category-items-cell"><?php echo $posizione++; ?></td>
                                <?php
                                if ($mod === 70) {
                                    if (isset($squadra->girone)) {
                                        echo '<td class="category-items-cell">' . htmlspecialchars($squadra->girone) . '</td>';
                                    } else {
                                        echo '<td class="category-items-cell">' . htmlspecialchars($stats['girone']) . '</td>';
                                    }
                                }
                                ?>
                                <td class="category-items-cell">
                                    <div style="border-radius:50px; background-color:<?php echo $color1; ?>"><span
                                            style="color:<?php echo $color2; ?>"><?php
                                               if (isset($squadra->squadra)) {
                                                   echo htmlspecialchars(Competizione::getArticleTitleById($squadra->squadra));
                                               } else {
                                                   echo htmlspecialchars(Competizione::getArticleTitleById($stats['squadra']));
                                               }
                                               ?>
                                        </span></div>
                                </td>
                                <td class="category-items-cell"><?php echo isset($stats['punti']) ? $stats['punti'] : 0; ?></td>
                                <td class="category-items-cell"><?php echo isset($stats['giocate']) ? $stats['giocate'] : 0; ?></td>
                                <td class="category-items-cell"><?php echo isset($stats['vinte']) ? $stats['vinte'] : 0; ?></td>
                                <td class="category-items-cell"><?php echo isset($stats['pari']) ? $stats['pari'] : 0; ?></td>
                                <td class="category-items-cell"><?php echo isset($stats['perse']) ? $stats['perse'] : 0; ?></td>
                                <td class="category-items-cell"><?php echo isset($stats['golFatti']) ? $stats['golFatti'] : 0; ?>
                                </td>
                                <td class="category-items-cell"><?php echo isset($stats['golSubiti']) ? $stats['golSubiti'] : 0; ?>
                                </td>
                                <td class="category-items-cell">
                                    <?php echo isset($stats['differenza']) ? $stats['differenza'] : 0; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (!empty($andamento) && !$checkgol): ?>
            <div class="table-responsive my-5 andamento">
                <?php
                $maxGiornate = max(array_map(function ($squadra) {
                    return count($squadra['risultati']);
                }, $andamento));
                ?>
                <table class="table table-striped table-bordered text-center">
                    <thead>
                        <tr>
                            <?php
                            $squadraprec = null;
                            $colspan = 0;
                            for ($giornata = 1; $giornata <= $maxGiornate; $giornata++) {
                                // Raccogli tutti i risultati della giornata corrente
                                $giornataRisultati = array_column($andamento, 'risultati', $giornata);

                                // Filtra solo i risultati validi per la giornata
                                $giornataRisultati = array_filter(array_column($giornataRisultati, $giornata), 'is_numeric');

                                // Ottieni il massimo valore della giornata
                                $maxRisultato = !empty($giornataRisultati) ? max($giornataRisultati) : null;

                                // Conta quante squadre hanno ottenuto il massimo valore
                                $conteggioMax = array_count_values($giornataRisultati)[$maxRisultato] ?? 0;

                                // Identifica la squadra leader, se esiste
                                $leader = null;
                                if ($conteggioMax === 1) {
                                    foreach ($andamento as $squadra) {
                                        if (isset($squadra['risultati'][$giornata]) && $squadra['risultati'][$giornata] == $maxRisultato) {
                                            $leader = $squadra['squadra'];
                                            break;
                                        }
                                    }
                                }

                                if ($leader === $squadraprec) {
                                    // Incrementa il colspan per giornate consecutive della stessa squadra
                                    $colspan++;
                                } else {
                                    // Stampa il td precedente se cambia la squadra o non c'è leader
                                    if ($squadraprec !== null) {
                                        $cf = Competizione::getCustomFields($squadraprec);
                                        $colors = !empty($cf[1]) ? $cf[1]->value : '#000000';
                                        $colort = !empty($cf[2]) ? $cf[2]->value : '#ffffff';
                                        echo '<th colspan="' . $colspan . '" style="background-color:' . $colors . '; color:' . $colort . '; font-size:1em; font-weight:bold;">' . Competizione::abbreviaNomeSquadra(Competizione::getArticleTitleById($squadraprec)) . '</th>';
                                    } elseif ($colspan > 0) {
                                        // Caso di leader assente: spazio vuoto
                                        echo '<th colspan="' . $colspan . '" style="background-color:#d3d3d3; color:#000000; font-size:1em; font-weight:bold;"></th>';
                                    }

                                    // Reset per la nuova squadra (o leader assente)
                                    $squadraprec = $leader;
                                    $colspan = 1;
                                }
                            }

                            // Stampa il td finale per l'ultima squadra o periodo vuoto
                            if ($squadraprec !== null) {
                                $cf = Competizione::getCustomFields($squadraprec);
                                $colors = !empty($cf[1]) ? $cf[1]->value : '#000000';
                                $colort = !empty($cf[2]) ? $cf[2]->value : '#ffffff';
                                echo '<th colspan="' . $colspan . '" style="background-color:' . $colors . '; color:' . $colort . '; font-size:1em; font-weight:bold;">' . Competizione::abbreviaNomeSquadra(Competizione::getArticleTitleById($squadraprec)) . '</th>';
                            } elseif ($colspan > 0) {
                                // Caso finale di leader assente
                                echo '<th colspan="' . $colspan . '" style="background-color:#d3d3d3; color:#000000; font-size:1em; font-weight:bold;"></th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php for ($giornata = 1; $giornata <= $maxGiornate; $giornata++): ?>
                                <td class="px-0"
                                    style="background-color:#8a8a8a; color:#ffffff;font-size:1em;font-weight:bold;min-width:25px;">
                                    <div><?php echo $giornata; ?></div>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    </tbody>
                </table>


                <table class="table table-striped table-bordered text-center category-table">
                    <thead class="thead-dark">
                        <tr>
                            <?php ($mod === 70) ? $colspan = 2 : $colspan = 1; ?>

                            <td class="fw-bold" colspan="<?php echo $colspan; ?>"><?php echo ucfirst($view); ?></td>
                            <td class="fw-bold" colspan="<?php echo Competizione::getGiornate($tablePartite) + 1; ?>">Giornate
                            </td>
                        </tr>
                        <tr>
                            <?php if ($mod === 70)
                                echo '<th class="fw-bold">Gironi</th>' ?>
                                <th class="category-header-logo">Squadra</th>
                                <?php
                            // Trova il numero massimo di giornate
                    
                            for ($giornata = 1; $giornata <= $maxGiornate; $giornata++): ?>
                                <th class="category-header-logo"><?php echo $giornata; ?>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($andamento as $squadra): ?>
                            <?php
                            $cf = Competizione::getCustomFields($squadra['squadra']);
                            // Retrieve color values with defaults
                            $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                            $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                            ?>
                            <tr>
                                <?php if ($mod === 70)
                                    echo '<td class="category-items-cell">' . Competizione::getGironeBySquadraId($squadra['squadra'], $tablePartite) . '</td>' ?>
                                    <td class="category-items-cell">
                                        <div style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                        <span style="color:<?php echo $color2; ?>">
                                            <?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra['squadra'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <?php for ($giornata = 1; $giornata <= $maxGiornate; $giornata++): ?>
                                    <td class="category-items-cell">
                                        <?php echo isset($squadra['risultati'][$giornata]) ? $squadra['risultati'][$giornata] : ""; ?>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($gir && !$checkgol): ?>
            <?php
            for ($i = 1; $i <= $gironi; $i++) {
                $classifica = Competizione::getClassificaGironi($tableStatistiche, $i);
                ?>
                <div class="table-responsive my-5">
                    <table class="table table-striped table-bordered text-center category-table">
                        <thead class="thead-dark">
                            <tr>
                                <td class="fw-bold" colspan="2">Girone <?php echo $i; ?></td>
                                <td class="fw-bold" colspan="8"><?php echo "Totale"; ?></td>
                            </tr>
                            <tr>
                                <th class="category-header-logo">#</th>
                                <th class="category-header-logo">Squadra</th>
                                <th class="category-header-logo">Pt</th>
                                <th class="category-header-logo">G</th>
                                <th class="category-header-logo">V</th>
                                <th class="category-header-logo">N</th>
                                <th class="category-header-logo">P</th>
                                <th class="category-header-logo">GF</th>
                                <th class="category-header-logo">GS</th>
                                <th class="category-header-logo">DR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $posizione = 1;
                            ($competizione->gironi != 0) ? $passatipergirone=$competizione->fase_finale/$competizione->gironi:$passatipergirone=0;
                            foreach ($classifica as $squadra):
                                // Calcola le statistiche
                                $stats = Competizione::calculateStatistics($squadra, $view, $ar, $tablePartite);

                                if (isset($squadra->squadra)) {
                                    $cf = Competizione::getCustomFields($squadra->squadra);
                                } else {
                                    $cf = Competizione::getCustomFields($stats['squadra']);
                                }

                                // Retrieve color values with defaults
                                $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                                $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                                if($posizione<=$passatipergirone) $bgcolor = "#00ff00";
                                else $bgcolor = "#ff0000";
                                ?>
                                <tr class="classificagironi" style="background-color: <?php echo $bgcolor; ?>;">
                                    <td class="category-items-cell"><?php echo $posizione++; ?></td>
                                    <td class="category-items-cell">
                                        <div style="border-radius:50px; background-color:<?php echo $color1; ?>"><span
                                                style="color:<?php echo $color2; ?>"><?php
                                                   if (isset($squadra->squadra)) {
                                                       echo htmlspecialchars(Competizione::getArticleTitleById($squadra->squadra));
                                                   } else {
                                                       echo htmlspecialchars(Competizione::getArticleTitleById($stats['squadra']));
                                                   }
                                                   ?>
                                            </span></div>
                                    </td>
                                    <td class="category-items-cell"><?php echo isset($stats['punti']) ? $stats['punti'] : 0; ?></td>
                                    <td class="category-items-cell"><?php echo isset($stats['giocate']) ? $stats['giocate'] : 0; ?></td>
                                    <td class="category-items-cell"><?php echo isset($stats['vinte']) ? $stats['vinte'] : 0; ?></td>
                                    <td class="category-items-cell"><?php echo isset($stats['pari']) ? $stats['pari'] : 0; ?></td>
                                    <td class="category-items-cell"><?php echo isset($stats['perse']) ? $stats['perse'] : 0; ?></td>
                                    <td class="category-items-cell"><?php echo isset($stats['golFatti']) ? $stats['golFatti'] : 0; ?>
                                    </td>
                                    <td class="category-items-cell"><?php echo isset($stats['golSubiti']) ? $stats['golSubiti'] : 0; ?>
                                    </td>
                                    <td class="category-items-cell">
                                        <?php echo isset($stats['differenza']) ? $stats['differenza'] : 0; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            ?>

        <?php endif; ?>


    </div>
    <?php
}
?>