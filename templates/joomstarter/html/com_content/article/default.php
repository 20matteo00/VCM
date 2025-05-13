<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomstarter\Helpers\Competizione;
$baseUrl = Uri::base();

// Creiamo un oggetto per l'articolo attuale
$app = Factory::getApplication();
$db = Factory::getDbo();
$user = Factory::getUser();
$userId = $user->id;
$id = (int) $this->item->id;
$groups = $user->get('groups');
$isadmin = in_array(8, $groups);
$customFields = Competizione::getCustomFields($id);

// Assegniamo i valori ai colori, alla forza e all'immagine
$color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000'; // Colore di sfondo del titolo
$color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff'; // Colore del testo
$strength = !empty($customFields[3]) ? $customFields[3]->value : 'N/A'; // Forza di default

$params = $this->item->params;

// ... (il tuo codice PHP esistente)
$stato = Competizione::getCategoriaTag($id);
// Ottieni l'immagine dell'articolo
$images = json_decode($this->item->images);
$imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : '/images/default.webp';

// Rimuovi eventuali parametri dall'URL dell'immagine
$imageSrc = strtok($imageSrc, '#'); // Questo restituirà solo la parte prima di '#'

// Stampa l'immagine per il DOM
?>
<div class="com-content-article item-page<?php echo $this->pageclass_sfx; ?>">
    <meta itemprop="inLanguage"
        content="<?php echo ($this->item->language === '*') ? $app->get('language') : $this->item->language; ?>">

    <div class="row">
        <div class="col-md-8 my-3">
            <?php if ($this->params->get('show_title')): ?>
                <div class="com-content-article__header  text-center"
                    style="background-color: <?php echo $color1; ?>;border-radius:50px;">
                    <h1 class="com-content-article__title" style="color: <?php echo $color2; ?>;">
                        <?php echo $this->escape($this->item->title); ?>
                    </h1>
                </div>
            <?php endif; ?>
            <div class="com-content-article__body" style="color: <?php echo $color2; ?>;">
                <?php echo $this->item->text; ?>
            </div>
            <div class="com-content-article__strength">
                <span class="h4 fw-bold">Valore: <?php echo $strength; ?>Mln €</span>
            </div>
            <br>

            <div class="com-content-article__metadata">
                <?php
                // Verifica se la categoria è presente
                if (!empty($this->item->catid)) {
                    $categories = '<a class="campionato" href="' . Route::_('index.php?option=com_content&view=category&id=' . $this->item->catid) . '">' . $this->escape($this->item->category_title) . '</a>';
                }
                echo '<span class="h4 fw-bold">Campionato: ' . $categories . '</span>';
                ?>
            </div>
            <br>

            <div class="com-content-article__metadata">
                <?php
                // Verifica se la categoria è presente
                if ($stato !== null) {
                    $tag = '<a class="campionato" href="' . htmlspecialchars($stato['link']) . '">' . htmlspecialchars($stato['title']) . '</a>';
                }
                echo '<span class="h4 fw-bold">Stato: ' . $tag . '</span>';
                ?>
            </div>
            <br>
            <?php
            if ($isadmin || $this->item->catid == 71) {
                ?>
                <span class="h4 fw-bold"><a class="campionato"
                        href="<?php echo $baseUrl; ?>index.php/modifica-squadra?id=<?php echo $id; ?>&catid=<?php echo $this->item->catid; ?>&modifica=modifica">Modifica</a></span>
                <?php
            }
            ?>
        </div>
        <div class="col-md-4  text-center my-3">
            <div class="com-content-article__image">
                <img id="articleImage" src="<?php echo htmlspecialchars($imageSrc); ?>"
                    alt="<?php echo htmlspecialchars($this->item->title); ?>">
            </div>
        </div>
    </div>

    
    <?php
    $c = Competizione::getAllCompetizioni($id, $userId, 0);
    if ($c != null):


        ?>
        <div class="accordion my-5" id="competizioniAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingcompetizioni">
                    <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapsecompetizioni" aria-expanded="false" aria-controls="collapsecompetizioni">
                        Competizioni
                    </button>
                </h2>
                <div id="collapsecompetizioni" class="accordion-collapse collapse" aria-labelledby="headingcompetizioni"
                    data-bs-parent="#competizioniAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <?php
                            for ($i = 0; $i < count($c); $i++) {
                                $competizione = Competizione::getCompetizioneById($c[$i], $userId);
                                echo "
                            <div class='col-12 col-sm-6 col-md-4 col-lg-3 mb-4'>
                                <div class='text-center p-3'>
                                    <a style='border-radius:50px;' href='" . $baseUrl . "index.php/visualizza-competizione?id=" . $competizione->id . "' class='btn btn-outline-dark w-100'>
                                        " . $c[$i] . " - " . $competizione->nome_competizione . "
                                    </a>
                                </div>
                            </div>
                        ";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    endif;
    ?>
    <div class="accordion my-5" id="archivioAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingarchivio">
                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsearchivio" aria-expanded="false" aria-controls="collapsearchivio">
                    Archivio
                </button>
            </h2>
            <div id="collapsearchivio" class="accordion-collapse collapse" aria-labelledby="headingarchivio"
                data-bs-parent="#archivioAccordion">
                <div class="accordion-body">
                    <?php
                    $c = Competizione::getAllCompetizioni($id, $userId, 0);
                    echo '<div class="row text-center">';
                    for ($i = 0; $i < count($c); $i++) {
                        $tablePartite = Competizione::getTablePartite($c[$i]);
                        $partite = Competizione::getPartitePerSquadra($id, $tablePartite);

                        $competizione = Competizione::getCompetizioneById($c[$i], $userId);
                        echo '<div class="col-12 my-4">';
                        echo '<h3>' . htmlspecialchars($c[$i]) . " - " . htmlspecialchars($competizione->nome_competizione) . '</h3>';
                        echo '<table class="table table-striped table-bordered">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Giornata</th>';
                        echo '<th>Partita</th>';
                        echo '<th>Risultato</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        if (!empty($partite)) {
                            foreach ($partite as $partita) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($partita->giornata) . '</td>';
                                echo '<td>' . Competizione::getArticleTitleById(htmlspecialchars($partita->squadra1)) . " - " . Competizione::getArticleTitleById(htmlspecialchars($partita->squadra2)) . '</td>';
                                if ($partita->gol1 !== null && $partita->gol2 !== null) {
                                    echo '<td>' . htmlspecialchars($partita->gol1) . " - " . htmlspecialchars($partita->gol2) . '</td>';
                                } else {
                                    echo '<td> - </td>';
                                }
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr>
                                <td colspan="3" class="text-center">Nessuna partita trovata</td>
                              </tr>';
                        }

                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo "</div>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion my-5" id="statisticheAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingstatistiche">
                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsestatistiche" aria-expanded="false" aria-controls="collapsestatistiche">
                    Statistiche
                </button>
            </h2>
            <div id="collapsestatistiche" class="accordion-collapse collapse" aria-labelledby="headingstatistiche"
                data-bs-parent="#statisticheAccordion">
                <div class="accordion-body overflow-auto px-0 px-md-4">
                    <?php
                    $modalitaArray = [68, 69, 70];
                    $totali = [
                        'Win' => 0,
                        'comp' => 0,
                        'countpartite' => 0,
                        'vc' => 0,
                        'nc' => 0,
                        'pc' => 0,
                        'gfc' => 0,
                        'gsc' => 0,
                        'vt' => 0,
                        'nt' => 0,
                        'pt' => 0,
                        'gft' => 0,
                        'gst' => 0,
                    ];
                    $recordPerModalita = [];

                    foreach ($modalitaArray as $mod) {
                        $Win = $countpartite = 0;
                        $vc = $nc = $pc = $gfc = $gsc = $vt = $nt = $pt = $gft = $gst = 0;
                        $comp = 0;
                        $c = Competizione::getAllCompetizioni($id, $userId, $mod);
                        for ($i = 0; $i < count($c); $i++) {
                            $tableStatistiche = Competizione::getTableStatistiche($c[$i]);
                            $tablePartite = Competizione::getTablePartite($c[$i]);
                            $countpartite += count(Competizione::getPartitePerSquadra($id, $tablePartite));
                            $stats = Competizione::getStats($tableStatistiche, $id);
                            $comp++;
                            // Accumula i valori statistici per la modalità corrente
                            $vc += $stats[0]->VC;
                            $nc += $stats[0]->NC;
                            $pc += $stats[0]->PC;
                            $gfc += $stats[0]->GFC;
                            $gsc += $stats[0]->GSC;
                            $vt += $stats[0]->VT;
                            $nt += $stats[0]->NT;
                            $pt += $stats[0]->PT;
                            $gft += $stats[0]->GFT;
                            $gst += $stats[0]->GST;

                            $competizione = Competizione::getCompetizioneById($c[$i], $userId);
                            $winner = Competizione::checkWinner($tablePartite, $tableStatistiche, $id, $mod);
                            if ($winner)
                                $Win++;
                        }

                        // Calcolo i totali della modalità
                        $gc = $vc + $nc + $pc;
                        $gt = $vt + $nt + $pt;
                        $dc = $gfc - $gsc;
                        $dt = $gft - $gst;
                        $d = $dc + $dt;

                        // Memorizza i valori per la modalità corrente
                        $recordPerModalita[$mod] = [
                            'Competizioni Vinte' => $Win,
                            'Competizioni Giocate' => $comp,
                            //Totale
                            'Giocate Totali' => $countpartite,
                            'Vinte Totali' => $vc + $vt,
                            'Pareggiate Totali' => $nc + $nt,
                            'Perse Totali' => $pc + $pt,
                            'Gol Fatti Totali' => $gfc + $gft,
                            'Gol Subiti Totali' => $gsc + $gst,
                            'Differenza Reti Totale' => $d,
                            // Casa
                            'Giocate Casa' => $gc,
                            'Vinte Casa' => $vc,
                            'Pareggiate Casa' => $nc,
                            'Perse Casa' => $pc,
                            'Gol Fatti Casa' => $gfc,
                            'Gol Subiti Casa' => $gsc,
                            'Differenza Reti Casa' => $dc,
                            //Trasferta
                            'Giocate Trasferta' => $gt,
                            'Vinte Trasferta' => $vt,
                            'Pareggiate Trasferta' => $nt,
                            'Perse Trasferta' => $pt,
                            'Gol Fatti Trasferta' => $gft,
                            'Gol Subiti Trasferta' => $gst,
                            'Differenza Reti Trasferta' => $dt,
                        ];

                        // Somma ai totali generali
                        foreach ($totali as $key => &$value) {
                            $value += ${$key};
                        }
                    }
                    // Array per i totali
                    $recordTotali = [
                        'Competizioni Vinte' => $totali['Win'] . "/" . ($recordPerModalita[68]["Competizioni Giocate"] + $recordPerModalita[69]["Competizioni Giocate"]),
                        'Competizioni Giocate' => $totali['comp'],
                        //Totale
                        'Giocate Totali' => $totali['countpartite'],
                        'Vinte Totali' => $totali['vc'] + $totali['vt'],
                        'Pareggiate Totali' => $totali['nc'] + $totali['nt'],
                        'Perse Totali' => $totali['pc'] + $totali['pt'],
                        'Gol Fatti Totali' => $totali['gfc'] + $totali['gft'],
                        'Gol Subiti Totali' => $totali['gsc'] + $totali['gst'],
                        'Differenza Reti Totale' => $totali['gfc'] - $totali['gsc'] + $totali['gft'] - $totali['gst'],
                        // Casa
                        'Giocate Casa' => $totali['vc'] + $totali['nc'] + $totali['pc'],
                        'Vinte Casa' => $totali['vc'],
                        'Pareggiate Casa' => $totali['nc'],
                        'Perse Casa' => $totali['pc'],
                        'Gol Fatti Casa' => $totali['gfc'],
                        'Gol Subiti Casa' => $totali['gft'],
                        'Differenza Reti Casa' => $totali['gfc'] - $totali['gsc'],
                        //Trasferta
                        'Giocate Trasferta' => $totali['vt'] + $totali['nt'] + $totali['pt'],
                        'Vinte Trasferta' => $totali['vt'],
                        'Pareggiate Trasferta' => $totali['nt'],
                        'Perse Trasferta' => $totali['pt'],
                        'Gol Fatti Trasferta' => $totali['gft'],
                        'Gol Subiti Trasferta' => $totali['gst'],
                        'Differenza Reti Trasferta' => $totali['gft'] - $totali['gst'],
                    ];
                    $i = 0;
                    ?>

                    <table class="table table-striped table-bordered text-center">
                        <thead>
                            <tr>
                                <th colspan="2">Record</th>
                                <th>Campionato</th>
                                <th>Eliminazione</th>
                                <th>Champions</th>
                                <th>Totale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recordTotali as $label => $value): ?>
                                <?php
                                $color = "";
                                if ($i === 3 || $i === 10 || $i === 17)
                                    $color = "Green";
                                elseif ($i === 4 || $i === 11 || $i === 18)
                                    $color = "Orange";
                                elseif ($i === 5 || $i === 12 || $i === 19)
                                    $color = "Red";
                                elseif ($i === 6 || $i === 13 || $i === 20)
                                    $color = "Lime";
                                elseif ($i === 7 || $i === 14 || $i === 21)
                                    $color = "LightCoral";
                                ?>
                                <tr>
                                    <?php
                                    if ($i === 0)
                                        echo "<td class='align-middle fw-bold' rowspan='2'>Generale</td>";
                                    elseif ($i === 2)
                                        echo "<td class='align-middle fw-bold' rowspan='7'>Totale</td>";
                                    elseif ($i === 9)
                                        echo "<td class='align-middle fw-bold' rowspan='7'>Casa</td>";
                                    elseif ($i === 16)
                                        echo "<td class='align-middle fw-bold' rowspan='7'>Trasferta</td>";
                                    ?>
                                    <td class="fw-bold"><?php echo $label; ?></td>
                                    <?php foreach ($modalitaArray as $mod): ?>
                                        <?php
                                        if ($recordPerModalita[$mod][$label] < 0 && ($i === 8 || $i === 15 || $i === 22))
                                            $color = "Crimson";
                                        elseif ($recordPerModalita[$mod][$label] > 0 && ($i === 8 || $i === 15 || $i === 22))
                                            $color = "Chartreuse";
                                        if ($mod == 69 && $label == "Competizioni Vinte") {
                                            echo "<td class='fw-bold' colspan='2' style='color:" . $color . ";'>" . $recordPerModalita[$mod][$label] . "</td>";
                                        } elseif ($mod == 70 && $label == "Competizioni Vinte") {
                                            echo "";
                                        } else {
                                            echo "<td class='fw-bold' style='color:" . $color . ";'>" . $recordPerModalita[$mod][$label] . "</td>";
                                        }
                                        ?>
                                    <?php endforeach; ?>
                                    <?php
                                    if ($recordTotali[$label] < 0 && ($i === 8 || $i === 15 || $i === 22))
                                        $color = "Crimson";
                                    elseif ($recordTotali[$label] > 0 && ($i === 8 || $i === 15 || $i === 22))
                                        $color = "Chartreuse";
                                    ?>
                                    <td class="fw-bold" style='color:<?php echo $color; ?>;'>
                                        <?php echo $recordTotali[$label]; ?>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <?php echo $this->item->event->afterDisplayContent; ?>
</div>