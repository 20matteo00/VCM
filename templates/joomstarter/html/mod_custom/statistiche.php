<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomstarter\Helpers\Competizione;

$user = Factory::getUser();
$userId = $user->id;

if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    if($_GET['module_id'] != 119) {
        header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=119");
        exit;
    }
    $tableStatistiche = Competizione::getTableStatistiche($idcomp);
    $tablePartite = Competizione::getTablePartite($idcomp);
    $competizione = Competizione::getCompetizioneById($idcomp, $userId);
    $ar = $competizione->andata_ritorno;
    $mod = $competizione->modalita;
    $squadre = json_decode($competizione->squadre, true); // Decodifica JSON in array
    $squadre = Competizione::getSquadreOrdinate($squadre);
    $checkgol = Competizione::checkGolNull($tablePartite);
    $record = [
        'Maggior Numero di Vittorie Consecutive',
        'Maggior Numero di Pareggi Consecutivi',
        'Maggior Numero di Sconfitte Consecutive',
        'Maggior Numero di Vittorie Consecutive in Casa',
        'Maggior Numero di Pareggi Consecutivi in Casa',
        'Maggior Numero di Sconfitte Consecutive in Casa',
        'Maggior Numero di Vittorie Consecutive in Trasferta',
        'Maggior Numero di Pareggi Consecutivi in Trasferta',
        'Maggior Numero di Sconfitte Consecutive in Trasferta',
        'Partita Vinta con Maggior Scarto di Goal',
        'Partita Persa con Maggior Scarto di Goal',
        'Partita con Maggior Numero di Goal',
    ];
    $general = [
        'Partite Totali',
        'Gol Totali',
        'Maggior Numero di Vittorie',
        'Minor Numero di Vittorie',
        'Maggior Numero di Pareggi',
        'Minor Numero di Pareggi',
        'Maggior Numero di Sconfitte',
        'Minor Numero di Sconfitte',
        'Miglior Attacco',
        'Peggior Attacco',
        'Miglior Difesa',
        'Peggior Difesa',
        'Miglior Differenza Reti',
        'Peggior Differenza Reti',
    ];
    // Ottieni la classifica
    $classifica = Competizione::getClassifica($tableStatistiche);
    $numsquadre = count($classifica);

    // Determina la vista in base al POST
    if (isset($_POST['Generali'])) {
        $view = 'Generali';
    } elseif (isset($_POST['Individuali'])) {
        $view = 'Individuali';
    } elseif (isset($_POST['Elenco'])) {
        $view = 'Elenco';
    } elseif (!isset($view)) {
        $view = 'Generali'; // Default view if none is set
    }
    ?>
    <div class="container statistiche mybar">
        <form method="post" action="">
            <div class="container p-2">
                <div class="row justify-content-between">
                    <input type="hidden" name="module_id" value="119">
                    <div class="col-6 col-md-4 mb-2">
                        <button type="submit" name="Generali" class="btn btn-info w-100">Generali</button>
                    </div>
                    <div class="col-6 col-md-4 mb-2">
                        <button type="submit" name="Individuali" class="btn btn-info w-100">Individuali</button>
                    </div>
                    <div class="col-6 col-md-4 mb-2">
                        <button type="submit" name="Elenco" class="btn btn-info w-100">Elenco Partite</button>
                    </div>
                </div>
            </div>
        </form>


        <?php if ($view === 'Individuali' || $view === 'Elenco'): ?>
            <div class="text-center my-5">
                <div class="row">
                    <?php foreach ($squadre as $squadra): ?>
                        <?php
                        $cf = Competizione::getCustomFields($squadra);
                        // Retrieve color values with defaults
                        $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                        $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                        ?>
                        <div class="col-12 col-md-6 col-lg-2 my-3" style="min-width:150px;">
                            <form action="" method="post">
                                <input type="hidden" name="squadra" value="<?php echo $squadra; ?>">
                                <input type="hidden" name="module_id" value="119">
                                <input type="hidden" name="<?php echo htmlspecialchars($view); ?>"
                                    value="<?php echo htmlspecialchars($view); ?>">
                                <button type="submit" class="btn w-100" name="submit">
                                    <div style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                        <span class="fs-5" style="color:<?php echo $color2; ?>">
                                            <?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra)); ?>
                                        </span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php
}

// Handle form submission to retain view
if (isset($_POST['submit'])) {
    $module_ID = $_POST['module_id'];
    $squadra = $_POST['squadra'];
    $vieww = $_POST[$view];

    if ($vieww === 'Individuali') {
        $matches = Competizione::getPartitePerSquadra($squadra, $tablePartite);
        ?>
        <h1 class="text-center fw-bold">
            <?php
            if ($mod === 70) {
                echo Competizione::getArticleTitleById($squadra) . " - Girone: " . Competizione::getGironeBySquadraId($squadra, $tablePartite);
            } else {
                echo Competizione::getArticleTitleById($squadra);
            }
            ?>
        </h1>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th class="category-header-logo" scope="col">Record</th>
                    <th class="category-header-logo" scope="col">#: Giornate</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through $record and display them
                foreach ($record as $index => $recordItem) {
                    ?>
                    <tr>
                        <td class="category-items-cell"><?php echo htmlspecialchars($recordItem); ?></td>
                        <td class="category-items-cell">
                            <?php
                            echo Competizione::getRecordIndividual($squadra, $tablePartite, $index);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <?php
    } elseif ($vieww === 'Elenco') {// Get the matches for the selected team
        $matches = Competizione::getPartitePerSquadra($squadra, $tablePartite);
        ?>
        <h1 class="text-center fw-bold">
            <?php
            if ($mod === 70) {
                echo Competizione::getArticleTitleById($squadra) . " - Girone: " . Competizione::getGironeBySquadraId($squadra, $tablePartite);
            } else {
                echo Competizione::getArticleTitleById($squadra);
            }
            ?>
        </h1>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th class="category-header-logo" scope="col">Giornata</th>
                    <th class="category-header-logo" scope="col">Partita</th>
                    <th class="category-header-logo" scope="col">Risultato</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through $matches and display them
                foreach ($matches as $match) {
                    ?>
                    <tr>
                        <td class="category-items-cell"><?php echo htmlspecialchars($match->giornata); ?></td>
                        <td class="category-items-cell">
                            <?php
                            echo htmlspecialchars(Competizione::getArticleTitleById($match->squadra1)) . " - " .
                                htmlspecialchars(Competizione::getArticleTitleById($match->squadra2));
                            ?>
                        </td>
                        <td class="category-items-cell">
                            <?php
                            if (!is_null($match->gol1) && !is_null($match->gol2)) {
                                echo htmlspecialchars($match->gol1) . " - " . htmlspecialchars($match->gol2);
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }
} elseif (isset($_POST['Generali'])) {
    $matches = Competizione::getPartite($tablePartite);
    ?>
    <div class="text-center my-5">

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th class="category-header-logo" scope="col">Record</th>
                    <th class="category-header-logo" scope="col">#: Squadre (Giornate)
                        <?php if ($mod === 70)
                            echo "- Girone"; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through $record and display them
                foreach ($general as $index => $recordItem) {
                    ?>
                    <tr>
                        <td class="category-items-cell"><?php echo htmlspecialchars($recordItem); ?></td>
                        <td class="category-items-cell">
                            <?php
                            echo Competizione::getGeneral($tablePartite, $tableStatistiche, $index);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <?php
                // Loop through $record and display them
                foreach ($record as $index => $recordItem) {
                    if ($index === 9)
                        continue;
                    elseif ($index === 10)
                        $recordItem = "Partita con Maggior Scarto di Goal";
                    ?>
                    <tr>
                        <td class="category-items-cell"><?php echo htmlspecialchars($recordItem); ?></td>
                        <td class="category-items-cell">
                            <?php
                            echo Competizione::getRecord($squadre, $tablePartite, $index, $mod);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>