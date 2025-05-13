<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;
$baseurl = URI::base();
?>
<div style=" margin-bottom:100px;">
    <?php
    // Verifica se l'ID è presente nei parametri GET
    if (isset($_GET['id'])) {
        // Ottieni l'ID della competizione in modo sicuro, convertendolo in un intero
        $idcomp = (int) $_GET['id'];

        // Recupera la competizione utilizzando la funzione
        $competizione = Competizione::getCompetizioneById($idcomp, $userId);
        $nome = $competizione->nome_competizione;
        $mod = $competizione->modalita;
        $ar = $competizione->andata_ritorno;
        $finita = $competizione->finita;
        if ($finita === 1)
            $disabled = "disabled";
        else
            $disabled = "";

        $gironi = $competizione->gironi;
        $squadreJson = $competizione->squadre;
        // Decodifica la stringa JSON in un array
        $squadre = json_decode($squadreJson, true);
        $numsquadre = 0;
        if (!empty($squadre)) {
            $numsquadre = count($squadre);
        }
        // Controlla se la competizione è stata trovata
        if ($competizione) {
            $nomemodalita = Competizione::getCategoryNameById($competizione->modalita);
            Competizione::CreaTabelleCompetizione($idcomp, $squadre);
            $tablePartite = Competizione::getTablePartite($idcomp);
            $tableStatistiche = Competizione::getTableStatistiche($idcomp);
            if ($mod == 68) {
                Competizione::GeneraCampionato($squadre, $tablePartite, $ar, false, null);
            } elseif ($mod == 69) {
                Competizione::GeneraEliminazione($squadre, $tablePartite, $ar);
            } elseif ($mod == 70) {
                Competizione::GeneraChampions($squadre, $tablePartite, $ar, $gironi);
            }
            Competizione::GeneraStatistiche($squadre, $tableStatistiche, $tablePartite, $mod);
            // Visualizza i dettagli della competizione
            echo '<h1 class="text-center fw-bold h1 mb-5">' . htmlspecialchars($competizione->nome_competizione) . '</h1>';
            ?>
            <form method="post" action="">
                <div class="container p-2 fixed-lg">
                    <div class="container">
                        <div class="row justify-content-between mybar">
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                                <button type="submit" name="module_id" value="116"
                                    class="btn btn-success w-100"><span class="bi bi-calendar"></span> Calendario</button>
                            </div>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                                <button type="submit" name="module_id" value="117"
                                    class="btn btn-success w-100"><span class="bi bi-trophy"></span> Classifica</button>
                            </div>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                                <button type="submit" name="module_id" value="118"
                                    class="btn btn-success w-100"><span class="bi bi-table"></span> Tabellone</button>
                            </div>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                                <button type="submit" name="module_id" value="119"
                                    class="btn btn-success w-100"><span class="bi bi-bar-chart"></span> Statistiche</button>
                            </div>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                                <button type="submit" name="simulation" class="btn btn-warning w-100" <?php echo $disabled; ?>>Simula</button>
                            </div>
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                                <button type="submit" name="elimination" class="btn btn-danger w-100" <?php echo $disabled; ?>>Elimina</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php
            // Verifica se è stato inviato un ID modulo
            if (isset($_POST['module_id'])) {
                $modulo = $_POST['module_id'];
                $module = ModuleHelper::getModuleById($modulo);
                echo ModuleHelper::renderModule($module);
            } elseif (isset($_GET['module_id'])) {
                $modulo = $_GET['module_id'];
                $module = ModuleHelper::getModuleById($modulo);
                echo ModuleHelper::renderModule($module);
            }
            if (isset($_POST['simulation'])) {
                $simpar = Competizione::getPartite($tablePartite);
                $module_ID = $_GET['module_id'];
                if ($mod !== 69) {
                    foreach ($simpar as $partita) {
                        if($partita->gol1!==null || $partita->gol2!==null)continue;
                        $cf1 = Competizione::getCustomFields($partita->squadra1);
                        $cf2 = Competizione::getCustomFields($partita->squadra2);
                        $forza1 = !empty($cf1[3]) ? $cf1[3]->value : 0;
                        $forza2 = !empty($cf2[3]) ? $cf2[3]->value : 0;
                        $ris = Competizione::ris($forza1, $forza2);
                        $gol1 = $ris['squadra1'];
                        $gol2 = $ris['squadra2'];
                        $db = Factory::getDbo();
                        $query = $db->getQuery(true)
                            ->update($db->quoteName($tablePartite))
                            ->set([
                                'gol1 = ' . $db->quote($gol1),
                                'gol2 = ' . $db->quote($gol2)
                            ])
                            ->where([
                                'squadra1 = ' . $db->quote($partita->squadra1),
                                'squadra2 = ' . $db->quote($partita->squadra2)
                            ]);
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
                header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
                exit;
            }
            if (isset($_POST['elimination'])) {
                $module_ID = $_GET['module_id'];
                $db = Factory::getDbo();

                // Prepara la query per impostare a NULL i gol della giornata specificata
                $query = $db->getQuery(true)
                    ->update($db->quoteName($tablePartite))
                    ->set([
                        $db->quoteName('gol1') . ' = NULL',
                        $db->quoteName('gol2') . ' = NULL'
                    ]);

                // Esegui la query per aggiornare i gol
                $db->setQuery($query);
                $db->execute();
                if ($mod == 69) {
                    $gio = 1;
                    if ($ar == 1)
                        $gio += 1;

                    // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
                    $deleteQuery = $db->getQuery(true)
                        ->delete($db->quoteName($tablePartite))
                        ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

                    // Esegui la query per eliminare le partite successive
                    $db->setQuery($deleteQuery);
                    $db->execute();
                }
                header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID");
                exit;
            }
            ?>

            <?php
            $numpartitevalide = Competizione::getNumeroPartite($tablePartite);
            $totpart = 0;
            if ($mod === 68) {
                $totgior = ($ar === 0) ? $numsquadre - 1 : ($numsquadre - 1) * 2;
                $totpart = $totgior * ($numsquadre / 2);
            } elseif ($mod === 69) {
                $totpart = ($ar === 0) ? $numsquadre - 1 : ($numsquadre - 1) * 2 - 1;
            } elseif ($mod === 70) {
                $totpartpergir = $numsquadre / $gironi;
                $totgior = ($ar === 0) ? $totpartpergir - 1 : ($totpartpergir - 1) * 2;
                $totpart = $totgior * ($totpartpergir / 2) * $gironi;
            }
            
            if ($mod === 69) {
                $partita = Competizione::getUltimaPartita($tablePartite);
                // Controlla se la partita è stata trovata e determina il vincitore
                if ($partita) {
                    $winner = "";
                    if ($partita->gol1 > $partita->gol2) {
                        $winner = $partita->squadra1;
                    } elseif ($partita->gol1 < $partita->gol2) {
                        $winner = $partita->squadra2;
                    }
                }
            }
            // $checknome = Competizione::CheckNome($nome . " - Fase Finale");
            if ($numpartitevalide === (int) $totpart && $finita === 0 && ($mod != 69 || ($mod == 69 && $winner != ""))) {
                ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center" role="alert">
                    <span><?php echo text::_('JOOM_COMPLIMENTI') ?></span>
                    <form action="" method="post">
                        <button class="btn btn-warning" name="closecomp"><?php echo text::_('JOOM_CHIUDICOMP') ?></button>
                    </form>
                </div>
                <?php
            } elseif ($finita === 1 && $mod === 70 /* && !$checknome */) {
                ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center" role="alert">
                    <span><?php echo text::_('JOOM_FASEFINALE') ?></span>
                    <form action="" method="post">
                        <button class="btn btn-success" name="fasefinale"><?php echo text::_('JOOM_FF') ?></button>
                    </form>
                </div>
                <?php
            }
            ?>
            <?php
        } else{
            header("location: " . $baseurl);
            exit;
        }
        if (isset($_POST['closecomp'])) {
            $module_ID = $_GET['module_id'];
            Competizione::setCompetizioneFinita($idcomp);
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID");
            exit;
        }
        if (isset($_POST['fasefinale'])) {
            Competizione::CreaFaseFinale($idcomp, $userId, $tableStatistiche);
            // Costruisci l'URL completo per "competizioni in corso"
            $url = Route::_('index.php?Itemid=106');
            header("Location: " . $url);
            exit;
        }
    } 
    ?>
</div>