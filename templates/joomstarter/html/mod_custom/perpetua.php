<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomstarter\Helpers\Competizione;

$baseUrl = Uri::base();
isset($_GET['catid']) ? $tipo = $_GET['catid'] : $tipo = 0;
if ($tipo == 71) {
    header("location: " . $baseUrl . "index.php/campionati");
    exit;
}

isset($_GET['view']) ? $view = (int) $_GET['view'] : $view = 0;

// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;

// Recupera le competizioni per tipo
$competizioni = Competizione::getCompetizioniPerTipo($userId, $tipo);
// Array in cui accumulare le statistiche per ogni squadra
$perpetua = [];
$count = 1;
// Ottieni l'oggetto database di Joomla
$db = Factory::getDbo();

// Ciclo su ogni competizione
foreach ($competizioni as $comp) {
    // Nome della tabella statistiche per questa competizione
    $tableName = Competizione::getTableStatistiche($comp->id);
    $stats = Competizione::getStats($tableName);

    // Accumula i dati per ogni squadra
    foreach ($stats as $stat) {
        // Assumiamo che "squadra" contenga il nome o l'identificativo della squadra
        $teamName = $stat->squadra;

        // Inizializza la voce se non esiste
        if (!isset($perpetua[$teamName])) {
            $perpetua[$teamName] = [
                'id' => $teamName,
                'count' => 0,

                'GC' => 0,
                'PtC' => 0,
                'VC' => 0,
                'NC' => 0,
                'PC' => 0,
                'GFC' => 0,
                'GSC' => 0,
                'DRC' => 0,

                'GT' => 0,
                'PtT' => 0,
                'VT' => 0,
                'NT' => 0,
                'PT' => 0,
                'GFT' => 0,
                'GST' => 0,
                'DRT' => 0,

                'G' => 0,
                'Pt' => 0,
                'V' => 0,
                'N' => 0,
                'P' => 0,
                'GF' => 0,
                'GS' => 0,
                'DR' => 0,
            ];
        }
        // Somma i valori per ogni colonna (convertiti in intero per sicurezza)
        $perpetua[$teamName]['VC'] += (int) $stat->VC;
        $perpetua[$teamName]['NC'] += (int) $stat->NC;
        $perpetua[$teamName]['PC'] += (int) $stat->PC;
        $perpetua[$teamName]['GFC'] += (int) $stat->GFC;
        $perpetua[$teamName]['GSC'] += (int) $stat->GSC;


        $perpetua[$teamName]['VT'] += (int) $stat->VT;
        $perpetua[$teamName]['NT'] += (int) $stat->NT;
        $perpetua[$teamName]['PT'] += (int) $stat->PT;
        $perpetua[$teamName]['GFT'] += (int) $stat->GFT;
        $perpetua[$teamName]['GST'] += (int) $stat->GST;



        $perpetua[$teamName]['GC'] = $perpetua[$teamName]['VC'] + $perpetua[$teamName]['NC'] + $perpetua[$teamName]['PC'];
        $perpetua[$teamName]['GT'] = $perpetua[$teamName]['VT'] + $perpetua[$teamName]['NT'] + $perpetua[$teamName]['PT'];
        $perpetua[$teamName]['PtC'] = $perpetua[$teamName]['VC'] * 3 + $perpetua[$teamName]['NC'];
        $perpetua[$teamName]['PtT'] = $perpetua[$teamName]['VT'] * 3 + $perpetua[$teamName]['NT'];
        $perpetua[$teamName]['DRC'] = $perpetua[$teamName]['GFC'] - $perpetua[$teamName]['GSC'];
        $perpetua[$teamName]['DRT'] = $perpetua[$teamName]['GFT'] - $perpetua[$teamName]['GST'];

        $perpetua[$teamName]['G'] = $perpetua[$teamName]['GC'] + $perpetua[$teamName]['GT'];
        $perpetua[$teamName]['Pt'] = $perpetua[$teamName]['PtC'] + $perpetua[$teamName]['PtT'];
        $perpetua[$teamName]['V'] = $perpetua[$teamName]['VC'] + $perpetua[$teamName]['VT'];
        $perpetua[$teamName]['N'] = $perpetua[$teamName]['NC'] + $perpetua[$teamName]['NT'];
        $perpetua[$teamName]['P'] = $perpetua[$teamName]['PC'] + $perpetua[$teamName]['PT'];
        $perpetua[$teamName]['GF'] = $perpetua[$teamName]['GFC'] + $perpetua[$teamName]['GFT'];
        $perpetua[$teamName]['GS'] = $perpetua[$teamName]['GSC'] + $perpetua[$teamName]['GST'];
        $perpetua[$teamName]['DR'] = $perpetua[$teamName]['DRC'] + $perpetua[$teamName]['DRT'];

        $perpetua[$teamName]['count']++;
    }
}
uasort($perpetua, function ($a, $b) {
    // Prima, confronta il punteggio Pt in ordine decrescente
    if ($a['Pt'] != $b['Pt']) {
        return $b['Pt'] <=> $a['Pt'];
    }
    // In caso di parità, confronta DR in ordine decrescente
    if ($a['DR'] != $b['DR']) {
        return $b['DR'] <=> $a['DR'];
    }
    // Se DR è uguale, confronta GF in ordine decrescente
    if ($a['GF'] != $b['GF']) {
        return $b['GF'] <=> $a['GF'];
    }
    // Se ancora pari, confronta l'id squadra (o il nome) in ordine crescente
    // Assumiamo che ogni record contenga un campo 'id'; in caso contrario, puoi usare il nome
    if (isset($a['id'], $b['id'])) {
        return $a['id'] <=> $b['id'];
    }
    // Se non hai l'id, la funzione restituisce 0 (mantiene l'ordine attuale)
    return 0;
});
?>

<h1 class="text-center fw-bold h1 mb-5">Classifica Perpetua <?php echo Competizione::getCategoryNameById($tipo) ?></h1>
<div class="d-flex justify-content-center gap-3">
    <a href="index.php/classifica-perpetua?catid=<?= $tipo ?>&view=0" class="btn btn-info rounded-pill">Tutto</a>
    <a href="index.php/classifica-perpetua?catid=<?= $tipo ?>&view=1" class="btn btn-info rounded-pill">Totale</a>
    <a href="index.php/classifica-perpetua?catid=<?= $tipo ?>&view=2" class="btn btn-info rounded-pill">Casa</a>
    <a href="index.php/classifica-perpetua?catid=<?= $tipo ?>&view=3" class="btn btn-info rounded-pill">Trasferta</a>
</div>

<div class="table-responsive my-5">
    <table class="table table-striped table-bordered text-center category-table">
        <thead class="thead-dark">
            <tr>
                <td class="fw-bold" colspan="3">Rank</td>
                <?php if ($view === 0 || $view === 1): ?>
                    <td class="fw-bold" colspan="8">Totale</td>
                <?php endif; ?>

                <?php if ($view === 0 || $view === 2): ?>
                    <td class="fw-bold" colspan="8">Casa</td>
                <?php endif; ?>

                <?php if ($view === 0 || $view === 3): ?>
                    <td class="fw-bold" colspan="8">Trasferta</td>
                <?php endif; ?>
            </tr>
            <tr>
                <th class="category-header-logo" style="cursor: pointer;">#</th>
                <th class="category-header-logo" style="cursor: pointer;">Squadra</th>
                <th class="category-header-logo" style="cursor: pointer;">N°</th>
                <?php if ($view === 0 || $view === 1): ?>
                    <th class="category-header-logo" style="cursor: pointer;">Pt</th>
                    <th class="category-header-logo" style="cursor: pointer;">G</th>
                    <th class="category-header-logo" style="cursor: pointer;">V</th>
                    <th class="category-header-logo" style="cursor: pointer;">N</th>
                    <th class="category-header-logo" style="cursor: pointer;">P</th>
                    <th class="category-header-logo" style="cursor: pointer;">GF</th>
                    <th class="category-header-logo" style="cursor: pointer;">GS</th>
                    <th class="category-header-logo" style="cursor: pointer;">DR</th>
                <?php endif; ?>

                <?php if ($view === 0 || $view === 2): ?>
                    <th class="category-header-logo" style="cursor: pointer;">Pt</th>
                    <th class="category-header-logo" style="cursor: pointer;">G</th>
                    <th class="category-header-logo" style="cursor: pointer;">V</th>
                    <th class="category-header-logo" style="cursor: pointer;">N</th>
                    <th class="category-header-logo" style="cursor: pointer;">P</th>
                    <th class="category-header-logo" style="cursor: pointer;">GF</th>
                    <th class="category-header-logo" style="cursor: pointer;">GS</th>
                    <th class="category-header-logo" style="cursor: pointer;">DR</th>
                <?php endif; ?>

                <?php if ($view === 0 || $view === 3): ?>
                    <th class="category-header-logo" style="cursor: pointer;">Pt</th>
                    <th class="category-header-logo" style="cursor: pointer;">G</th>
                    <th class="category-header-logo" style="cursor: pointer;">V</th>
                    <th class="category-header-logo" style="cursor: pointer;">N</th>
                    <th class="category-header-logo" style="cursor: pointer;">P</th>
                    <th class="category-header-logo" style="cursor: pointer;">GF</th>
                    <th class="category-header-logo" style="cursor: pointer;">GS</th>
                    <th class="category-header-logo" style="cursor: pointer;">DR</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($perpetua as $squadra => $data) { ?>
                <?php
                $cf = Competizione::getCustomFields($squadra);
                $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                $link = Competizione::getArticleUrlById($squadra);
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td>
                        <a href="<?php echo $link ?>">
                            <div style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                <span style="color:<?php echo $color2; ?>">
                                    <?php echo Competizione::getArticleTitleById($squadra); ?>
                                </span>
                            </div>
                        </a>
                    </td>
                    <td><?php echo $data['count']; ?></td>
                    <?php if ($view === 0 || $view === 1): ?>
                        <td><?php echo $data['Pt']; ?></td>
                        <td><?php echo $data['G']; ?></td>
                        <td><?php echo $data['V']; ?></td>
                        <td><?php echo $data['N']; ?></td>
                        <td><?php echo $data['P']; ?></td>
                        <td><?php echo $data['GF']; ?></td>
                        <td><?php echo $data['GS']; ?></td>
                        <td><?php echo $data['DR']; ?></td>
                    <?php endif; ?>

                    <?php if ($view === 0 || $view === 2): ?>
                        <td><?php echo $data['PtC']; ?></td>
                        <td><?php echo $data['GC']; ?></td>
                        <td><?php echo $data['VC']; ?></td>
                        <td><?php echo $data['NC']; ?></td>
                        <td><?php echo $data['PC']; ?></td>
                        <td><?php echo $data['GFC']; ?></td>
                        <td><?php echo $data['GSC']; ?></td>
                        <td><?php echo $data['DRC']; ?></td>
                    <?php endif; ?>

                    <?php if ($view === 0 || $view === 3): ?>
                        <td><?php echo $data['PtT']; ?></td>
                        <td><?php echo $data['GT']; ?></td>
                        <td><?php echo $data['VT']; ?></td>
                        <td><?php echo $data['NT']; ?></td>
                        <td><?php echo $data['PT']; ?></td>
                        <td><?php echo $data['GFT']; ?></td>
                        <td><?php echo $data['GST']; ?></td>
                        <td><?php echo $data['DRT']; ?></td>
                    <?php endif; ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>