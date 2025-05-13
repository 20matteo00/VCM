<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;



if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    if ($_GET['module_id'] != 116) {
        header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=116");
        exit;
    }
    $competizione = Competizione::getCompetizioneById($idcomp, $userId);
    $partecipants = $competizione->partecipanti;
    $finita = $competizione->finita;
    $mod = $competizione->modalita;
    $ar = $competizione->andata_ritorno;
    $squadre = $competizione->squadre;
    ($partecipants % 2 == 0) ? $pari = true : $pari = false;
    ($finita === 1) ? $disabled = "disabled" : $disabled = "";
    $tablePartite = Competizione::getTablePartite($idcomp);
    $giornateRaw = Competizione::getGiornateByCompetizioneId($idcomp, $tablePartite);
    // Riorganizza le partite in giornate
    $giornate = [];
    foreach ($giornateRaw as $partita) {
        $cf1 = Competizione::getCustomFields($partita->squadra1);
        $cf2 = Competizione::getCustomFields($partita->squadra2);
        $forza1 = !empty($cf1[3]) ? $cf1[3]->value : 0;
        $forza2 = !empty($cf2[3]) ? $cf2[3]->value : 0;
        $giornate[$partita->giornata][] = [
            'squadra1' => $partita->squadra1,
            'squadra2' => $partita->squadra2,
            'gol1' => $partita->gol1,
            'gol2' => $partita->gol2,
            'giornata' => $partita->giornata,
            'girone' => $partita->girone,
            'forza1' => $forza1,
            'forza2' => $forza2,
        ];
    } ?>
    <div class="container calendario">
        <div class="row">
            <?php foreach ($giornate as $index => $partite): ?>
                <div class="col-12 col-lg-6" id="<?php echo $index; ?>">
                    <div class="card mb-4">
                        <div class="card-header p-2">
                            <h5 class="text-center m-0 fw-bold">GIORNATA <?php echo $index; ?></h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($partite as $i => $partita): ?>
                                <?php
                                $s1 = Competizione::getArticleTitleById($partita['squadra1']);
                                $s2 = Competizione::getArticleTitleById($partita['squadra2']);
                                $cf1 = Competizione::getCustomFields($partita['squadra1']);
                                $cf2 = Competizione::getCustomFields($partita['squadra2']);
                                $colors1 = !empty($cf1[1]) ? $cf1[1]->value : '#000000';
                                $colort1 = !empty($cf1[2]) ? $cf1[2]->value : '#ffffff';
                                $colors2 = !empty($cf2[1]) ? $cf2[1]->value : '#000000';
                                $colort2 = !empty($cf2[2]) ? $cf2[2]->value : '#ffffff';
                                $forza1 = !empty($cf1[3]) ? $cf1[3]->value : 0;
                                $forza2 = !empty($cf2[3]) ? $cf2[3]->value : 0;
                                $gol1 = isset($partita['gol1']) ? $partita['gol1'] : '';
                                $gol2 = isset($partita['gol2']) ? $partita['gol2'] : '';
                                $girone = isset($partita['girone']) ? $partita['girone'] : '';
                                ?>
                                <div class="d-flex my-3 mx-2 fw-bold align-items-center myinput ">
                                    <?php if ($mod === 70): ?>
                                        <div class="p-1 text-center me-2"
                                            style="border-radius:50px; width: 32px; background-color:var(--nero);">
                                            <span style="color:var(--bianco);"><?php echo htmlspecialchars($girone); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="p-1 text-center calendarow"
                                        style="border-radius:50px; background-color: <?php echo $colors1; ?>;">
                                        <span style="color: <?php echo $colort1; ?>;"><?php echo htmlspecialchars($s1); ?></span>
                                        <span class="forzahover"><?php echo $forza1; ?></span>
                                    </div>
                                    <div class="mx-3"></div>
                                    <div class="p-1 text-center calendarow"
                                        style="border-radius:50px; background-color: <?php echo $colors2; ?>;">
                                        <span style="color: <?php echo $colort2; ?>;"><?php echo htmlspecialchars($s2); ?></span>
                                        <span class="forzahover"><?php echo $forza2; ?></span>
                                    </div>
                                    <form action="" class="d-flex align-items-center ms-3" method="post">
                                        <input type="hidden" name="module_id" value="116">
                                        <input type="hidden" name="strength1" value="<?php echo $forza1; ?>">
                                        <input type="hidden" name="strength2" value="<?php echo $forza2; ?>">
                                        <input type="hidden" name="ar" value="<?php echo $ar; ?>">
                                        <input type="hidden" name="modalita" value="<?php echo $mod; ?>">
                                        <input type="hidden" name="giornata" value="<?php echo $index; ?>">
                                        <input type="hidden" name="squadra1" value="<?php echo $partita['squadra1']; ?>">
                                        <input type="hidden" name="squadra2" value="<?php echo $partita['squadra2']; ?>">
                                        <input type="number" id="gol1-<?php echo $index . '-' . $i; ?>" name="gol1"
                                            class="form-control me-2 text-center" value="<?php echo $gol1; ?>"
                                            onclick="selezionaInput(this)" <?php echo $disabled; ?>>
                                        <input type="number" id="gol2-<?php echo $index . '-' . $i; ?>" name="gol2"
                                            class="form-control text-center" value="<?php echo $gol2; ?>"
                                            onclick="selezionaInput(this)" <?php echo $disabled; ?>>
                                        <?php //if ($mod !== 69): ?>
                                        <button type="submit" name="save" class="btn btn-success ms-2"
                                            style="width: 30px; height: 30px; border-radius: 50%;" <?php echo $disabled; ?>>
                                            <span class="bi bi-check2 text-white" style="font-size:25px;"></span>
                                        </button>
                                        <button type="submit" name="simulate" class="btn btn-warning ms-1"
                                            style="width: 30px; height: 30px; border-radius: 50%;" <?php echo $disabled; ?>>
                                            <span class="bi bi-magic text-white" style="font-size:25px;"></span>
                                        </button>
                                        <button type="submit" name="delete" class="btn btn-danger ms-1"
                                            style="width: 30px; height: 30px; border-radius: 50%;" <?php echo $disabled; ?>>
                                            <span class="bi bi-x text-white" style="font-size:25px;"></span>
                                        </button>
                                        <?php //endif; ?>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                            <?php
                            if ($pari === false) {
                                echo '<p class="text-center m-0 mt-5"> Riposa: ';

                                $sr = Competizione::getsquadramancante($tablePartite, $index, $squadre);
                                $srname = Competizione::getArticleTitleById($sr);
                                $cfr = Competizione::getCustomFields($sr);
                                $colorsr = !empty($cfr[1]) ? $cfr[1]->value : '#000000';
                                $colortr = !empty($cfr[2]) ? $cfr[2]->value : '#ffffff';
                                echo "<span class='py-2 px-4 fw-bold' style='border-radius:50px; background-color: " . $colorsr . "; color: " . $colortr . ";'>" . $srname . "</span>";

                                echo '</p>';
                            }
                            ?>
                        </div>
                        <div class="card-footer">
                            <form action="" class="p-2 d-flex justify-content-between" method="post"
                                onsubmit="updateAllGolValues(<?php echo $index; ?>)">
                                <input type="hidden" name="module_id" value="116">
                                <input type="hidden" name="ar" value="<?php echo $ar; ?>">
                                <input type="hidden" name="modalita" value="<?php echo $mod; ?>">
                                <input type="hidden" name="giornata" value="<?php echo $index; ?>">

                                <?php foreach ($partite as $i => $partita): ?>
                                    <input type="hidden" name="squadra1[]" value="<?php echo $partita['squadra1']; ?>">
                                    <input type="hidden" name="squadra2[]" value="<?php echo $partita['squadra2']; ?>">
                                    <input type="hidden" name="strength1[]" value="<?php echo $partita['forza1']; ?>">
                                    <input type="hidden" name="strength2[]" value="<?php echo $partita['forza2']; ?>">
                                    <input type="hidden" name="gol1[]" id="hidden-gol1-<?php echo $index . '-' . $i; ?>"
                                        value="<?php echo $gol1; ?>">
                                    <input type="hidden" name="gol2[]" id="hidden-gol2-<?php echo $index . '-' . $i; ?>"
                                        value="<?php echo $gol2; ?>">
                                <?php endforeach; ?>

                                <button type="submit" name="saveall" class="btn btn-success" style="width: 80px;" <?php echo $disabled; ?>>Salva</button>
                                <button type="submit" name="simulateall" class="btn btn-warning" style="width: 80px;" <?php echo $disabled; ?>>Simula</button>
                                <button type="submit" name="deleteall" class="btn btn-danger" style="width: 80px;" <?php echo $disabled; ?>>Elimina</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php
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
                    $cf1 = Competizione::getCustomFields($winner);
                    $colors = !empty($cf1[1]) ? $cf1[1]->value : '#000000';
                    $colort = !empty($cf1[2]) ? $cf1[2]->value : '#ffffff';
                    // Se c'è un vincitore, mostra la card
                    if ($winner !== "") {
                        ?>
                        <div class="col-12 col-lg-6" id="<?php echo $partita->giornata + 1; ?>">
                            <div class="card mb-4">
                                <div class="card-header p-2">
                                    <h5 class="text-center m-0 fw-bold">VINCITORE</h5>
                                </div>
                                <div class="card-body">
                                    <p class="p-1 text-center fw-bold m-auto"
                                        style="border-radius:50px; width:200px; background-color: <?php echo $colors; ?>;">
                                        <span
                                            style="color: <?php echo $colort; ?>;"><?php echo htmlspecialchars(Competizione::getArticleTitleById($winner)); ?></span>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <!-- Puoi aggiungere ulteriori dettagli qui, se necessario -->
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            ?>


        </div>
    </div>
    <?php
}
?>

<?php
if (isset($_POST['save'])) {
    $squadra1 = $_POST['squadra1'];
    $squadra2 = $_POST['squadra2'];
    $giornata = $_POST['giornata'];
    $mod = $_POST['modalita'];
    $ar = $_POST['ar'];
    if ($_POST['gol1'] != NULL) {
        $gol1 = $_POST['gol1'];
    } else
        $gol1 = 0;
    if ($_POST['gol2'] != NULL) {
        $gol2 = $_POST['gol2'];
    } else
        $gol2 = 0;
    $module_ID = $_POST['module_id'];
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->update($db->quoteName($tablePartite))
        ->set([
            'gol1 = ' . $db->quote($gol1),
            'gol2 = ' . $db->quote($gol2)
        ])
        ->where([
            'squadra1 = ' . $db->quote($squadra1),
            'squadra2 = ' . $db->quote($squadra2)
        ]);
    $db->setQuery($query);
    $db->execute();
    if ($mod == 69) {
        $gio = $giornata;
        if ($gio % 2 == 1 && $ar == 1)
            $gio += 1;

        // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
        $deleteQuery = $db->getQuery(true)
            ->delete($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

        // Esegui la query per eliminare le partite successive
        $db->setQuery($deleteQuery);
        $db->execute();
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
} elseif (isset($_POST['simulate'])) {
    $squadra1 = $_POST['squadra1'];
    $squadra2 = $_POST['squadra2'];
    $giornata = $_POST['giornata'];
    $mod = $_POST['modalita'];
    $ar = $_POST['ar'];
    $module_ID = $_POST['module_id'];
    $forza1 = $_POST['strength1'];
    $forza2 = $_POST['strength2'];
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
            'squadra1 = ' . $db->quote($squadra1),
            'squadra2 = ' . $db->quote($squadra2)
        ]);
    $db->setQuery($query);
    $db->execute();
    if ($mod == 69) {
        $gio = $giornata;
        if ($gio % 2 == 1 && $ar == 1)
            $gio += 1;

        // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
        $deleteQuery = $db->getQuery(true)
            ->delete($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

        // Esegui la query per eliminare le partite successive
        $db->setQuery($deleteQuery);
        $db->execute();
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
} elseif (isset($_POST['delete'])) {
    $squadra1 = $_POST['squadra1'];
    $squadra2 = $_POST['squadra2'];
    $giornata = $_POST['giornata'];
    $module_ID = $_POST['module_id'];
    $mod = $_POST['modalita'];
    $ar = $_POST['ar'];

    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->update($db->quoteName($tablePartite))
        ->set([
            $db->quoteName('gol1') . ' = NULL',
            $db->quoteName('gol2') . ' = NULL'
        ])
        ->where([
            $db->quoteName('squadra1') . ' = ' . $db->quote($squadra1),
            $db->quoteName('squadra2') . ' = ' . $db->quote($squadra2)
        ]);
    $db->setQuery($query);
    $db->execute();
    if ($mod == 69) {
        $gio = $giornata;
        if ($gio % 2 == 1 && $ar == 1)
            $gio += 1;

        // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
        $deleteQuery = $db->getQuery(true)
            ->delete($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

        // Esegui la query per eliminare le partite successive
        $db->setQuery($deleteQuery);
        $db->execute();
    }

    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
} elseif (isset($_POST['saveall'])) {
    $module_ID = $_POST['module_id'];
    $giornata = $_POST['giornata'];
    // Recupera i valori delle squadre e dei gol
    $squadre1 = $_POST['squadra1']; // Array di squadre1
    $squadre2 = $_POST['squadra2']; // Array di squadre2
    $gol1 = $_POST['gol1']; // Array di gol1
    $gol2 = $_POST['gol2']; // Array di gol2
    $mod = $_POST['modalita'];
    $ar = $_POST['ar'];

    // Assicurati che tutti gli array abbiano la stessa lunghezza
    $count = count($squadre1);

    if ($count === count($squadre2) && $count === count($gol1) && $count === count($gol2)) {
        $db = Factory::getDbo();

        for ($i = 0; $i < $count; $i++) {
            // Prepara i dati
            $s1 = $db->quote($squadre1[$i]);
            $s2 = $db->quote($squadre2[$i]);
            $g1 = is_numeric($gol1[$i]) ? $db->quote($gol1[$i]) : 0;
            $g2 = is_numeric($gol2[$i]) ? $db->quote($gol2[$i]) : 0;

            // Costruisci la query di aggiornamento
            $query = $db->getQuery(true)
                ->update($db->quoteName($tablePartite))
                ->set([
                    $db->quoteName('gol1') . ' = ' . $g1,
                    $db->quoteName('gol2') . ' = ' . $g2
                ])
                ->where([
                    $db->quoteName('squadra1') . ' = ' . $s1,
                    $db->quoteName('squadra2') . ' = ' . $s2,
                    $db->quoteName('giornata') . ' = ' . (int) $giornata // filtro per la giornata, se necessario
                ]);

            // Esegui la query
            $db->setQuery($query);
            $db->execute();
            if ($mod == 69) {
                $gio = $giornata;
                if ($gio % 2 == 1 && $ar == 1)
                    $gio += 1;

                // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
                $deleteQuery = $db->getQuery(true)
                    ->delete($db->quoteName($tablePartite))
                    ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

                // Esegui la query per eliminare le partite successive
                $db->setQuery($deleteQuery);
                $db->execute();
            }
        }
        $gio = $giornata + 1;
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$gio");
    exit;
} elseif (isset($_POST['simulateall'])) {
    $module_ID = $_POST['module_id'];
    $giornata = $_POST['giornata'];
    // Recupera i valori delle squadre e dei gol
    $squadre1 = $_POST['squadra1']; // Array di squadre1
    $squadre2 = $_POST['squadra2']; // Array di squadre2
    $forza1 = $_POST['strength1']; // Array di forza1
    $forza2 = $_POST['strength2']; // Array di forza2
    $gol1 = $_POST['gol1']; // Array di gol1
    $gol2 = $_POST['gol2']; // Array di gol2
    $mod = $_POST['modalita'];
    $ar = $_POST['ar'];

    // Assicurati che tutti gli array abbiano la stessa lunghezza
    $count = count($squadre1);
    var_dump($gol1);
    if ($count === count($squadre2) && $count === count($gol1) && $count === count($gol2)) {
        $db = Factory::getDbo();

        for ($i = 0; $i < $count; $i++) {
            // Prepara i dati
            $s1 = $db->quote($squadre1[$i]);
            $s2 = $db->quote($squadre2[$i]);
            $f1 = $forza1[$i]; // Rimuovi il quote() per lavorare con i numeri direttamente
            $f2 = $forza2[$i]; // Rimuovi il quote() per lavorare con i numeri direttamente
            if ($gol1[$i] !== "" && $gol2[$i] !== "")
                continue;
            $ris = Competizione::ris($f1, $f2);
            $g1 = $ris['squadra1'];
            $g2 = $ris['squadra2'];

            // Costruisci la query di aggiornamento
            $query = $db->getQuery(true)
                ->update($db->quoteName($tablePartite))
                ->set([
                    $db->quoteName('gol1') . ' = ' . $g1,
                    $db->quoteName('gol2') . ' = ' . $g2
                ])
                ->where([
                    $db->quoteName('squadra1') . ' = ' . $s1,
                    $db->quoteName('squadra2') . ' = ' . $s2,
                    $db->quoteName('giornata') . ' = ' . (int) $giornata // filtro per la giornata, se necessario
                ]);

            // Esegui la query
            $db->setQuery($query);
            $db->execute();
            if ($mod == 69) {
                $gio = $giornata;
                if ($gio % 2 == 1 && $ar == 1)
                    $gio += 1;

                // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
                $deleteQuery = $db->getQuery(true)
                    ->delete($db->quoteName($tablePartite))
                    ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

                // Esegui la query per eliminare le partite successive
                $db->setQuery($deleteQuery);
                $db->execute();
            }
        }
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
} elseif (isset($_POST['deleteall'])) {
    $giornata = $_POST['giornata'];
    $module_ID = $_POST['module_id'];
    $mod = $_POST['modalita'];
    $ar = $_POST['ar'];
    // Ottieni il database
    $db = Factory::getDbo();

    // Prepara la query per impostare a NULL i gol della giornata specificata
    $query = $db->getQuery(true)
        ->update($db->quoteName($tablePartite))
        ->set([
            $db->quoteName('gol1') . ' = NULL',
            $db->quoteName('gol2') . ' = NULL'
        ])
        ->where($db->quoteName('giornata') . ' = ' . (int) $giornata);

    // Esegui la query per aggiornare i gol
    $db->setQuery($query);
    $db->execute();
    if ($mod == 69) {
        $gio = $giornata;
        if ($gio % 2 == 1 && $ar == 1)
            $gio += 1;

        // Prepara una seconda query per eliminare tutte le partite dopo la giornata specificata
        $deleteQuery = $db->getQuery(true)
            ->delete($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' > ' . (int) $gio);

        // Esegui la query per eliminare le partite successive
        $db->setQuery($deleteQuery);
        $db->execute();
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
}
?>