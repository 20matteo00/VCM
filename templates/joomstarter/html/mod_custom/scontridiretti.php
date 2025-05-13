<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;
// Gestisci l'invio del form
$scontriDiretti = [];
if (isset($_POST['submit'])) {
    $squadra1 = (int) $_POST['squadra1'];
    $squadra2 = (int) $_POST['squadra2'];
    $luogo = (int) $_POST['luogo'];
    $modalita = (int) $_POST['modalita'];
    $scontriDiretti = Competizione::getScontriDiretti($squadra1, $squadra2, $luogo, $modalita, $userId);
} elseif (isset($_POST['reset'])) {
    $_POST = [];
}
$squadre = Competizione::getArticlesFromSubcategories(8, $userId);

?>

<div class="container my-5">
    <h1 class="text-center">Scontri Diretti tra Due Squadre</h1>
    <form method="POST" class="my-4">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Campo per la prima squadra -->
                <div class="col-md-3 col-12 mb-3">
                    <label for="squadra1" class="form-label fs-5 fw-bold">Squadra 1</label>
                    <select name="squadra1" id="squadra1" class="form-select form-select-lg">
                        <option value="0">-</option>
                        <?php
                        $selectedSquadra = isset($_POST['squadra1']) ? $_POST['squadra1'] : ''; // Puoi anche usare $_GET se necessario
                        foreach ($squadre as $squadra) {
                            // Imposta l'attributo "selected" se l'ID della squadra corrisponde al valore selezionato
                            $selected = ($squadra->id == $selectedSquadra) ? ' selected' : '';
                            $cf = Competizione::getCustomFields($squadra->id);
                            $colors = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                            $colort = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                            echo '<option style="background-color:' . $colors . '; color:' . $colort . '" value="' . $squadra->id . '"' . $selected . '>' . htmlspecialchars($squadra->title) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Campo per la seconda squadra -->
                <div class="col-md-3 col-12 mb-3">
                    <label for="squadra2" class="form-label fs-5 fw-bold">Squadra 2</label>
                    <select name="squadra2" id="squadra2" class="form-select form-select-lg">
                        <option value="0">-</option>
                        <?php
                        $selectedSquadra = isset($_POST['squadra2']) ? $_POST['squadra2'] : ''; // Puoi anche usare $_GET se necessario
                        foreach ($squadre as $squadra) {
                            $selected = ($squadra->id == $selectedSquadra) ? ' selected' : '';
                            $cf = Competizione::getCustomFields($squadra->id);
                            $colors = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                            $colort = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                            echo '<option style="background-color:' . $colors . '; color:' . $colort . '" value="' . $squadra->id . '"' . $selected . '>' . htmlspecialchars($squadra->title) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Campo per il luogo -->
                <div class="col-md-3 col-12 mb-3">
                    <label for="luogo" class="form-label fs-5 fw-bold">Luogo</label>
                    <select name="luogo" id="luogo" class="form-select form-select-lg">
                        <?php
                        $selectedluogo = isset($_POST['luogo']) ? (int) $_POST['luogo'] : 0;
                        $casa = $trasferta = "";
                        if ($selectedluogo === 1) {
                            $casa = "selected";
                        } elseif ($selectedluogo === 2) {
                            $trasferta = "selected";
                        }
                        ?>
                        <option value="0" <?php echo ($selectedluogo === 0) ? 'selected' : ''; ?>>Tutto</option>
                        <option value="1" <?php echo $casa; ?>>Casa</option>
                        <option value="2" <?php echo $trasferta; ?>>Trasferta</option>

                    </select>
                </div>

                <!-- Campo per la modalita -->
                <div class="col-md-3 col-12 mb-3">
                    <label for="modalita" class="form-label fs-5 fw-bold">Modalita</label>
                    <select name="modalita" id="modalita" class="form-select form-select-lg">
                        <?php
                        $selectedModalita = isset($_POST['modalita']) ? (int) $_POST['modalita'] : 0;
                        $camp = $elim = $champ = "";
                        if ($selectedModalita === 68) {
                            $camp = "selected";
                        } elseif ($selectedModalita === 69) {
                            $elim = "selected";
                        } elseif ($selectedModalita === 70) {
                            $champ = "selected";
                        }
                        ?>
                        <option value="0" <?php echo ($selectedModalita === 0) ? 'selected' : ''; ?>>Tutto</option>
                        <option value="68" <?php echo $camp; ?>>Campionato</option>
                        <option value="69" <?php echo $elim; ?>>Eliminazione</option>
                        <option value="70" <?php echo $champ; ?>>Champions</option>

                    </select>
                </div>

                <!-- Bottone per invio e reset sulla stessa riga -->
                <div class="col-12 text-center mt-4 d-flex justify-content-center">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg mx-2">Cerca</button>
                    <button type="submit" name="reset" class="btn btn-dark btn-lg mx-2">Pulisci</button>
                </div>

            </div>
        </div>
    </form>


    <?php if (!empty($scontriDiretti)): ?>
        <h2 class="my-4">Risultati degli Scontri Diretti</h2>
        <?php
        $partite = count($scontriDiretti);
        $gc1 = $gc2 = $vc1 = $vc2 = $nc1 = $nc2 = $pc1 = $pc2 = $gfc1 = $gfc2 = $gsc1 = $gsc2 = $dc1 = $dc2 = 0;
        $gt1 = $gt2 = $vt1 = $vt2 = $nt1 = $nt2 = $pt1 = $pt2 = $gft1 = $gft2 = $gst1 = $gst2 = $dt1 = $dt2 = 0;
        $g1 = $g2 = $v1 = $v2 = $n1 = $n2 = $p1 = $p2 = $gf1 = $gf2 = $gs1 = $gs2 = $d1 = $d2 = 0;
        foreach ($scontriDiretti as $scontro) {
            $partita = $scontro['partita']; // Dettagli della partita
    
            if ($partita->gol1 !== null && $partita->gol2 !== null) {
                // Se la squadra1 è la prima squadra della partita
                if ($squadra1 == $partita->squadra1 && $squadra2 == $partita->squadra2) {
                    if ($partita->gol1 > $partita->gol2) {
                        $vc1++; // Vittoria squadra 1
                        $pt2++; // Punti persi dalla squadra 2
                    } elseif ($partita->gol1 == $partita->gol2) {
                        $nc1++; // Pareggio squadra 1
                        $nt2++; // Pareggio squadra 2
                    } elseif ($partita->gol1 < $partita->gol2) {
                        $pc1++; // Partita persa squadra 1
                        $vt2++; // Vittoria squadra 2
                    }
                    // Aggiornamento statistiche per squadra1
                    $gfc1 += $partita->gol1; // Gol fatti squadra 1
                    $gsc1 += $partita->gol2; // Gol subiti squadra 1
                    $gft2 += $partita->gol2; // Gol fatti squadra 2
                    $gst2 += $partita->gol1; // Gol subiti squadra 2
                }

                // Se la squadra1 è la seconda squadra della partita
                elseif ($squadra1 == $partita->squadra2 && $squadra2 == $partita->squadra1) {
                    if ($partita->gol1 > $partita->gol2) {
                        $vc2++; // Vittoria squadra 1
                        $pt1++; // Punti persi dalla squadra 2
                    } elseif ($partita->gol1 == $partita->gol2) {
                        $nc2++; // Pareggio squadra 1
                        $nt1++; // Pareggio squadra 2
                    } elseif ($partita->gol1 < $partita->gol2) {
                        $pc2++; // Partita persa squadra 1
                        $vt1++; // Vittoria squadra 2
                    }
                    // Aggiornamento statistiche per squadra1
                    $gfc2 += $partita->gol1; // Gol fatti squadra 1
                    $gsc2 += $partita->gol2; // Gol subiti squadra 1
                    $gft1 += $partita->gol2; // Gol fatti squadra 2
                    $gst1 += $partita->gol1; // Gol subiti squadra 2
                }

                $gc1 = $vc1 + $nc1 + $pc1;
                $gt1 = $vt1 + $nt1 + $pt1;
                $gc2 = $vc2 + $nc2 + $pc2;
                $gt2 = $vt2 + $nt2 + $pt2;
                $dc1 = $gfc1 - $gsc1;
                $dt1 = $gft1 - $gst1;
                $dc2 = $gfc2 - $gsc2;
                $dt2 = $gft2 - $gst2;

                $g1 = $gc1 + $gt1;
                $v1 = $vc1 + $vt1;
                $n1 = $nc1 + $nt1;
                $p1 = $pc1 + $pt1;
                $gf1 = $gfc1 + $gft1;
                $gs1 = $gsc1 + $gst1;
                $d1 = $dc1 + $dt1;

                $g2 = $gc2 + $gt2;
                $v2 = $vc2 + $vt2;
                $n2 = $nc2 + $nt2;
                $p2 = $pc2 + $pt2;
                $gf2 = $gfc2 + $gft2;
                $gs2 = $gsc2 + $gst2;
                $d2 = $dc2 + $dt2;
            }
        }
        $class = "";
        $col = "";
        if ($luogo === 1 || $luogo === 2) {
            $class = "d-none";
            $col = "col-md-12";
        }
        // A questo punto avrai aggiornato tutte le statistiche relative alle due squadre ($squadra1 e $squadra2)            
        ?>
        <?php
        // Funzione per calcolare la percentuale con controllo sulla divisione per zero
        function calcolaPercentuale($parte, $totale)
        {
            if ($totale == 0) {
                return "0%"; // Evita la divisione per zero
            }
            return round(($parte / $totale) * 100, 2) . "%";
        }
        ?>

        <div class="row mb-4">
            <!-- Card per la prima squadra -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-sm border-light rounded">
                    <div class="card-header text-center bg-success text-white">
                        <h5 class="m-0"><?php echo Competizione::getArticleTitleById($squadra1); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12 <?php echo $col; ?>">
                                <h4 class="text-muted">Totale</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $g1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong>
                                        <span><?php echo $v1 . " (" . calcolaPercentuale($v1, $g1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong>
                                        <span><?php echo $n1 . " (" . calcolaPercentuale($n1, $g1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong>
                                        <span><?php echo $p1 . " (" . calcolaPercentuale($p1, $g1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gf1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gs1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $d1; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 <?php echo $class; ?>">
                                <h4 class="text-muted">Casa</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $gc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong>
                                        <span><?php echo $vc1 . " (" . calcolaPercentuale($vc1, $gc1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong>
                                        <span><?php echo $nc1 . " (" . calcolaPercentuale($nc1, $gc1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong>
                                        <span><?php echo $pc1 . " (" . calcolaPercentuale($pc1, $gc1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gfc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gsc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $dc1; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 <?php echo $class; ?>">
                                <h4 class="text-muted">Trasferta</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between"><strong>Giocate:</strong>
                                        <span><?php echo $gt1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Vinte:</strong>
                                        <span><?php echo $vt1 . " (" . calcolaPercentuale($vt1, $gt1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Pari:</strong>
                                        <span><?php echo $nt1 . " (" . calcolaPercentuale($nt1, $gt1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Perse:</strong>
                                        <span><?php echo $pt1 . " (" . calcolaPercentuale($pt1, $gt1) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Fatti:</strong>
                                        <span><?php echo $gft1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Subiti:</strong>
                                        <span><?php echo $gst1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Differenza Reti:</strong>
                                        <span><?php echo $dt1; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card per la seconda squadra -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-sm border-light rounded">
                    <div class="card-header text-center bg-success text-white">
                        <h5 class="m-0"><?php echo Competizione::getArticleTitleById($squadra2); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12 <?php echo $col; ?>">
                                <h4 class="text-muted">Totale</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $g2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong>
                                        <span><?php echo $v2 . " (" . calcolaPercentuale($v2, $g2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong>
                                        <span><?php echo $n2 . " (" . calcolaPercentuale($n2, $g2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong>
                                        <span><?php echo $p2 . " (" . calcolaPercentuale($p2, $g2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gf2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gs2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $d2; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 <?php echo $class; ?>">
                                <h4 class="text-muted">Casa</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $gc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong>
                                        <span><?php echo $vc2 . " (" . calcolaPercentuale($vc2, $gc2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong>
                                        <span><?php echo $nc2 . " (" . calcolaPercentuale($nc2, $gc2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong>
                                        <span><?php echo $pc2 . " (" . calcolaPercentuale($pc2, $gc2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gfc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gsc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $dc2; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 <?php echo $class; ?>">
                                <h4 class="text-muted">Trasferta</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between"><strong>Giocate:</strong>
                                        <span><?php echo $gt2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Vinte:</strong>
                                        <span><?php echo $vt2 . " (" . calcolaPercentuale($vt2, $gt2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Pari:</strong>
                                        <span><?php echo $nt2 . " (" . calcolaPercentuale($nt2, $gt2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Perse:</strong>
                                        <span><?php echo $pt2 . " (" . calcolaPercentuale($pt2, $gt2) . ")"; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Fatti:</strong>
                                        <span><?php echo $gft2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Subiti:</strong>
                                        <span><?php echo $gst2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Differenza Reti:</strong>
                                        <span><?php echo $dt2; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-striped table-bordered text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Competizione</th>
                    <th>Giornata</th>
                    <th>Partita</th>
                    <th>Risultato</th>
                    <th>Esito (1X2)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scontriDiretti as $scontro): ?>
                    <?php
                    $partita = $scontro['partita']; // Dettagli della partita
                    $competizionenome = $scontro['competizione']; // ID della competizione
                    $id = $scontro['id'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($id); ?></td>
                        <td><?php echo htmlspecialchars($competizionenome); ?></td>
                        <td><?php echo htmlspecialchars($partita->giornata); ?></td>
                        <td>
                            <?php 
                            $class1 = $class2 = "";
                            $esito = "X";
                            if($partita->gol1 > $partita->gol2) {
                                $class1 = "fw-bold";
                                $esito = "1";
                            } 
                            elseif($partita->gol1 < $partita->gol2) {
                                $class2="fw-bold";
                                $esito = "2";
                            } 
                            ?>
                            <?php echo "<span class='$class1'>" . htmlspecialchars(Competizione::getArticleTitleById($partita->squadra1)) . "</span>"; ?> vs
                            <?php echo "<span class='$class2'>" . htmlspecialchars(Competizione::getArticleTitleById($partita->squadra2)) . "</span>"; ?>
                        </td>
                        <td>
                            <?php if ($partita->gol1 !== null && $partita->gol2 !== null): ?>
                                <?php echo htmlspecialchars($partita->gol1); ?> - <?php echo htmlspecialchars($partita->gol2); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($partita->gol1 !== null && $partita->gol2 !== null): ?>
                                <?php echo $esito; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


    <?php else: ?>
        <p class="text-center">Nessun incontro trovato tra le due squadre.</p>
    <?php endif; ?>
</div>