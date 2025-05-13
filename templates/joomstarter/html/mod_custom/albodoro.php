<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomstarter\Helpers\Competizione;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
$baseUrl = Uri::base();
isset($_GET['catid']) ? $tipo = $_GET['catid'] : $tipo = 0;
if ($tipo == 71) {
    header("location: " . $baseUrl . "index.php/campionati");
    exit;
}
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;

// Recupera i parametri di paginazione
$limit = $input->getInt('limit', 5); // Numero di competizioni per pagina, default 5
$page = $input->getInt('page', 1); // Pagina corrente, default 1
$offset = ($page - 1) * $limit;

// Ottieni le competizioni e il numero totale di competizioni
$competizioni = Competizione::getCompetizioniPerTipo($userId, $tipo, $limit, $offset);
$totalCompetizioni = Competizione::countCompetizioniPerTipo($userId, $tipo);
if ($limit === 0)
    $limit = $totalCompetizioni;
$totalPages = ceil($totalCompetizioni / $limit);
$i = $totalCompetizioni - ($limit * ($page - 1));
$entrato = false;
$partecipants = $competizioni[0]->partecipanti;
$topnumber = isset($_GET['topnumber']) ? intval($_GET['topnumber']) : 3;
?>
<form method="get" action="">
    <div class="mb-3">
    </div>
</form>
<?php
// Visualizza i risultati in un formato HTML
if (!empty($competizioni)) { ?>
    <h1 class="text-center fw-bold">Albo D'oro <?php echo Competizione::getCategoryTitleById($tipo); ?></h1>
    <form action="" method="get" class="d-flex align-items-center justify-content-center gap-3 my-3">
    <div class="form-group mb-0">
        <label for="limit"><?php echo Text::_('Seleziona il numero di articoli per pagina'); ?></label>
        <select name="limit" id="limit" class="form-select" onchange="this.form.submit()">
            <option value="0" <?php echo $limit == 0 ? 'selected' : ''; ?>>Tutto</option>
            <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
            <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
            <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
        </select>
    </div>
    <div class="form-group mb-0">
        <label for="topnumber"><?php echo Text::_('Seleziona il numero di posizioni'); ?></label>
        <input type="number" class="form-control" name="topnumber" min="1" max="<?php echo $partecipants; ?>" value="<?php echo htmlspecialchars($topnumber); ?>" onchange="this.form.submit()">
    </div>
    <input type="hidden" name="catid" value="<?php echo $tipo; ?>">
</form>


    <div class="table-responsive category-table-container competizioni">
        <table class="table table-striped w-100 align-middle" style="min-width:800px;">
            <thead>
                <tr>
                    <th class="category-header-logo">Edizione</th>
                    <th class="category-header-logo">Nome</th>
                    <?php for ($j = 1; $j <= $topnumber; $j++): ?>
                        <th class="category-header-title"><?php echo $j; ?>° Posto</th>
                    <?php endfor; ?>

                </tr>
            </thead>
            <tbody class="allarticles">
                <?php foreach ($competizioni as $competizione):
                    $idcomp = $competizione->id;
                    $nomeCompetizione = $competizione->nome_competizione;
                    $nomeModalita = Competizione::getCategoryNameById($competizione->modalita);
                    $nomeCategoria = Competizione::getCategoryNameById($competizione->tipo);
                    $tablePartite = Competizione::getTablePartite($idcomp);
                    $tableStatistiche = Competizione::getTableStatistiche($idcomp);
                    $podio = Competizione::checkTop($tablePartite, $tableStatistiche, $topnumber);

                    $podioData = [];
                    foreach ($podio as $index => $squadra) {
                        $cf = Competizione::getCustomFields($squadra->squadra);
                        $podioData[] = [
                            'id' => $squadra->squadra,
                            'title' => Competizione::getArticleTitleById($squadra->squadra),
                            'colorBg' => $cf[1]->value,
                            'colorText' => $cf[2]->value,
                            'points' => ($squadra->VC + $squadra->VT) * 3 + ($squadra->NC + $squadra->NT),
                            'diff' => ($squadra->GFC + $squadra->GFT) - ($squadra->GSC + $squadra->GST),
                            'pos' => $index + 1,
                        ];
                    }
                    $entrato = true;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $i--; ?></td>
                        <td class="text-center">
                            <div class="text-center p-3">
                                <a href="<?php echo $baseUrl; ?>index.php/visualizza-competizione?id=<?php echo $idcomp; ?>"
                                    class="btn btn-outline-dark w-100" style="border-radius: 50px;">
                                    <?php echo $nomeCompetizione; ?>
                                </a>
                            </div>
                        </td>
                        <?php foreach ($podioData as $data): ?>
                            <td class="text-center" style="min-width:110px;">
                                <div style="background-color: <?php echo $data['colorBg']; ?>; border-radius: 50px;">
                                    <a class="d-block w-100"
                                        href="<?php echo Route::_(RouteHelper::getArticleRoute($data['id'], $competizione->tipo)); ?>"
                                        style="color: <?php echo $data['colorText']; ?>;">
                                        <?php echo $data['title']; ?>
                                    </a>
                                </div>
                                <?php echo "Punti: " . $data['points']; ?>
                                <br>
                                <?php echo "Diff. Reti: " . $data['diff']; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php } else {
    echo "<p class='h1 text-center'>" . text::_('JOOM_NESSUNA_COMPETIZIONE_PRESENTE') . "</p>";
}
/*<!-- Aggiungi i link di navigazione per la paginazione -->*/
if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">

            <!-- Link alla prima pagina -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo Route::_('index.php?catid=' . $tipo . '&page=1&limit=' . $limit); ?>">
                        <span class="icon-angle-double-left" aria-hidden="true"></span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><span class="icon-angle-double-left" aria-hidden="true"></span></span>
                </li>
            <?php endif; ?>

            <!-- Link alla pagina precedente -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?catid=' . $tipo . '&page=' . ($page - 1) . '&limit=' . $limit); ?>"
                        aria-label="Precedente">
                        <span class="icon-angle-left" aria-hidden="true"></span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><span class="icon-angle-left" aria-hidden="true"></span></span>
                </li>
            <?php endif; ?>

            <!-- Link alle pagine centrali -->
            <?php
            $start = max(1, $page - 5);
            $end = min($totalPages, $page + 5);

            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?catid=' . $tipo . '&page=' . $i . '&limit=' . $limit); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Link alla pagina successiva -->
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?catid=' . $tipo . '&page=' . ($page + 1) . '&limit=' . $limit); ?>"
                        aria-label="Successiva">
                        <span class="icon-angle-right" aria-hidden="true"></span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><span class="icon-angle-right" aria-hidden="true"></span></span>
                </li>
            <?php endif; ?>

            <!-- Link all'ultima pagina -->
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?catid=' . $tipo . '&page=' . $totalPages . '&limit=' . $limit); ?>">
                        <span class="icon-angle-double-right" aria-hidden="true"></span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><span class="icon-angle-double-right" aria-hidden="true"></span></span>
                </li>
            <?php endif; ?>

        </ul>
    </nav>
<?php endif; ?>


<?php
if ($entrato) {
    $allcompetizioni = Competizione::getCompetizioniPerTipo($userId, $tipo);
    $allcomp = [];
    foreach ($allcompetizioni as $competizione) {
        $idcomp = $competizione->id;
        $nomeCompetizione = $competizione->nome_competizione;
        $nomeModalita = Competizione::getCategoryNameById($competizione->modalita);
        $nomeCategoria = Competizione::getCategoryNameById($competizione->tipo);
        $tablePartite = Competizione::getTablePartite($idcomp);
        $tableStatistiche = Competizione::getTableStatistiche($idcomp);
        $podio = Competizione::checkTop($tablePartite, $tableStatistiche, $topnumber);

        $podioData = [];
        foreach ($podio as $index => $squadra) {
            $cf = Competizione::getCustomFields($squadra->squadra);
            $podioData[] = [
                'id' => $squadra->squadra,
                'title' => Competizione::getArticleTitleById($squadra->squadra),
                'colorBg' => $cf[1]->value,
                'colorText' => $cf[2]->value,
                'pos' => $index + 1,
            ];
        }
        $allcomp[] = $podioData;
    }
    // Array per raccogliere i risultati
    $result = [];
    // Iteriamo sull'array principale
    foreach ($allcomp as $competition) {
        foreach ($competition as $team) {
            $id = $team['id'];

            // Se la squadra non è ancora nel risultato, la inizializziamo
            if (!isset($result[$id])) {
                // Inizializza l'array del team con le informazioni di base
                $result[$id] = [
                    'id' => $id,
                    'title' => $team['title'],
                    'colorBg' => $team['colorBg'],
                    'colorText' => $team['colorText'],
                ];

                // Aggiungi i contatori dinamici count_pos_n fino a $topnumber
                for ($n = 1; $n <= $topnumber; $n++) {
                    $result[$id]["count_pos_$n"] = 0;  // Inizializza ogni contatore per le posizioni
                }
            }

            // Incrementiamo dinamicamente il contatore della posizione corrispondente
            $pos = $team['pos']; // Posizione del team

            // Verifica che la posizione sia valida
            if ($pos >= 1 && $pos <= $topnumber) {
                $countKey = "count_pos_$pos";  // Crea la chiave dinamicamente in base alla posizione
                $result[$id][$countKey]++; // Incrementa il contatore corrispondente
            }
        }
    }

    // Ordina l'array $result basato sui contatori per posizione
    usort($result, function ($a, $b) use ($topnumber) {  // Aggiungi "use" per passare topnumber alla funzione
        // Confronta ogni posizione da 1 fino a $topnumber
        for ($n = 1; $n <= $topnumber; $n++) {
            $key = "count_pos_$n"; // La chiave dinamica per ogni posizione

            // Se i valori per questa posizione sono diversi, ordina
            if ($a[$key] !== $b[$key]) {
                // Ordina in ordine decrescente: chi ha più punti va prima
                return $b[$key] <=> $a[$key];
            }
        }

        // Se tutte le posizioni sono uguali, restituiamo 0 (per non modificare l'ordine)
        return 0;
    });
    // Convertiamo in array semplice
    $result = array_values($result);
    ?>

    <div class="accordion my-5" id="archivioAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingarchivio">
                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsearchivio" aria-expanded="false" aria-controls="collapsearchivio">
                    Statistiche
                </button>
            </h2>
            <div id="collapsearchivio" class="accordion-collapse collapse" aria-labelledby="headingarchivio"
                data-bs-parent="#archivioAccordion">
                <div class="accordion-body">
                    <h3 class="text-center">Titoli per Squadra</h3>
                    <div class="table-responsive category-table-container competizioni">
                        <table class="table table-striped category-table">
                            <thead>
                                <tr>
                                    <th class="category-header-logo">Squadra</th>
                                    <?php for ($j = 1; $j <= $topnumber; $j++): ?>
                                        <th class="category-header-logo"><?php echo $j; ?>° Posto</th>
                                    <?php endfor; ?>
                                    <th class="category-header-logo">Top <?php echo $topnumber; ?></th>
                                </tr>
                            </thead>
                            <tbody class="allarticles">
                                <?php foreach ($result as $ris): ?>
                                    <tr>
                                        <td class="text-center">
                                            <div
                                                style="background-color: <?php echo $ris['colorBg']; ?>; border-radius: 50px; padding: 10px;">
                                                <a class="d-block w-100 fw-bold"
                                                    href="<?php echo Route::_(RouteHelper::getArticleRoute($ris['id'], $tipo)); ?>"
                                                    style="color: <?php echo $ris['colorText']; ?>;">
                                                    <?php echo $ris['title']; ?>
                                                    <?php
                                                    // Calcolare il numero di stelle piene
                                                    $star = intval($ris['count_pos_1'] / 10);

                                                    // Stampare le stelle
                                                    for ($i = 0; $i < $star; $i++) {
                                                        echo '<i class="bi bi-star-fill text-warning" style="font-size: 1rem;"></i>';
                                                    }
                                                    ?>
                                                </a>
                                            </div>
                                        </td>
                                        <?php $podi = $ris['count_pos_1'] + $ris['count_pos_2'] + $ris['count_pos_3']; ?>
                                        <?php
                                        // Calcola il totale dinamicamente sommando tutti i contatori delle posizioni
                                        $top = 0;
                                        for ($n = 1; $n <= $topnumber; $n++) {
                                            $key = "count_pos_$n"; // Crea la chiave dinamicamente
                                            $top += $ris[$key]; // Somma il valore della posizione
                                        }
                                        // Visualizza dinamicamente le celle per ogni posizione
                                        for ($n = 1; $n <= $topnumber; $n++) {
                                            $key = "count_pos_$n"; // Crea la chiave dinamicamente
                                            echo "<td class='text-center'>" . $ris[$key] . "</td>"; // Stampa la cella con il valore
                                        }
                                        ?>
                                        <td class="text-center"><strong><?php echo $top; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>