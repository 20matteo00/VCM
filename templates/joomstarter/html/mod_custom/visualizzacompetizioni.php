<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomstarter\Helpers\Competizione;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;
$baseUrl = Uri::base();

// Ottieni l'ID della voce di menu attiva
$menu = Factory::getApplication()->getMenu();
$activeMenuItem = $menu->getActive();
$menuItemId = $activeMenuItem ? $activeMenuItem->id : null;

// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;

// Recupera i parametri di paginazione
$limit = $input->getInt('limit', 5); // Numero di competizioni per pagina, default 5
$page = $input->getInt('page', 1); // Pagina corrente, default 1
$offset = ($page - 1) * $limit;

// Determina lo stato delle competizioni in base al menu attivo
$finita = null;
if ($menuItemId === 106) {
    $finita = 0;
} elseif ($menuItemId === 107) {
    $finita = 1;
}

// Ottieni le competizioni e il numero totale di competizioni
$competizioni = Competizione::getCompetizioniPerUtente($userId, $finita, $limit, $offset);
$totalCompetizioni = Competizione::countCompetizioniPerUtente($userId, $finita);
if ($limit === 0)
    $limit = $totalCompetizioni;

$totalPages = ceil($totalCompetizioni / $limit);


$pagconsentite = [106, 107];
if (in_array($menuItemId, $pagconsentite)) {
    // Visualizza i risultati in un formato HTML
    if (!empty($competizioni)) { ?>
        <h1 class="text-center fw-bold">Competizioni
            <?php echo ($menuItemId == 106) ? " in Corso" : " Finite"; ?>
        </h1>
        <form action="" method="get">
            <div class="form-group w-25 mx-auto mb-3">
                <label for="limit"><?php echo Text::_('Seleziona il numero di articoli per pagina'); ?></label>
                <select name="limit" id="limit" class="form-control" onchange="this.form.submit()">
                    <option value="0" <?php echo $limit == 0 ? 'selected' : ''; ?>>Tutto</option>
                    <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                    <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                </select>
            </div>
        </form>

        <div class="table-responsive category-table-container competizioni">
            <table class="table table-striped category-table">
                <thead>
                    <tr>
                        <th class="category-header-title">ID</th>
                        <th class="category-header-title">Nome Competizione</th>
                        <th class="category-header-title">Modalità</th>
                        <th class="category-header-title">Tipo</th>
                        <th class="category-header-title">Gironi</th>
                        <th class="category-header-title">Andata/Ritorno</th>
                        <th class="category-header-title">Partecipanti</th>
                        <th class="category-header-title">Fase Finale</th>
                        <th class="category-header-title">Squadre</th>
                        <th class="category-header-title">Azioni</th>
                    </tr>
                </thead>
                <tbody class="allarticles">
                    <?php foreach ($competizioni as $competizione):
                        // Decodifica la stringa JSON o PHP serializzata
                        $squadre = json_decode($competizione->squadre);
                        $idcomp = $competizione->id;
                        $nomemodalita = Competizione::getCategoryNameById($competizione->modalita);
                        $nomecategoria = Competizione::getCategoryNameById($competizione->tipo);
                        $categoryUrl = Route::_(RouteHelper::getCategoryRoute($competizione->tipo));
                        
                        if (($menuItemId == 106 && $competizione->finita == 0) || ($menuItemId == 107 && $competizione->finita == 1)): ?>
                            <tr>
                                <td class="category-title-cell"><?php echo htmlspecialchars($idcomp); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->nome_competizione); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($nomemodalita); ?></td>
                                <td class="category-title-cell"><a href="<?php echo $categoryUrl; ?>"><?php echo htmlspecialchars($nomecategoria); ?></a></td>
                                <td class="category-title-cell">
                                    <?php
                                    if ($competizione->modalita != 70) {
                                        echo "No";
                                    } else {
                                        echo htmlspecialchars($competizione->gironi);
                                    }
                                    ?>
                                </td>
                                <td class="category-title-cell"><?php echo ($competizione->andata_ritorno == 0) ? "No" : "Si"; ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->partecipanti); ?></td>
                                <td class="category-title-cell">
                                    <?php
                                    if ($competizione->modalita != 70) {
                                        echo "No";
                                    } else {
                                        echo htmlspecialchars($competizione->fase_finale);
                                    }
                                    ?>
                                </td>
                                <td class="category-title-cell">
                                    <div class="overflow-auto" style="max-height: 120px;">
                                        <?php $squadre = Competizione::getSquadreOrdinate($squadre); ?>
                                        <?php foreach ($squadre as $id):
                                            $customFields = Competizione::getCustomFields($id);
                                            $color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000';
                                            $color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff';
                                            $articleTitle = htmlspecialchars(Competizione::getArticleTitleById($id));
                                            $articleUrl = Competizione::getArticleUrlById($id); ?>
                                            <div class="p-1 mx-2 my-1"
                                                style="background-color:<?php echo $color1; ?>; display: inline-block; border-radius:50px;">
                                                <a class="h5 fw-bold px-2" style="color:<?php echo $color2; ?>"
                                                    href="<?php echo htmlspecialchars($articleUrl); ?>"><?php echo $articleTitle; ?></a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="category-title-cell">
                                    <form class="text-center" action="" method="post">
                                        <input type="hidden" value="<?php echo $idcomp; ?>" name="id">
                                        <button type="submit" class="btn btn-success btn-sm my-1" name="visualizza">Visualizza</button>
                                        <button type="submit" class="btn btn-warning btn-sm my-1" name="duplica">Duplica</button>
                                        <button type="submit" class="btn btn-danger btn-sm my-1" name="elimina">Elimina</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif;
                    endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php } else {
        echo "<p class='h1 text-center'>" . text::_('JOOM_NESSUNA_COMPETIZIONE_PRESENTE') . "</p>";
    }
}
/*<!-- Aggiungi i link di navigazione per la paginazione -->*/
if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">

            <!-- Link alla prima pagina -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?Itemid=' . $menuItemId . '&page=1&limit=' . $limit); ?>">
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
                        href="<?php echo Route::_('index.php?Itemid=' . $menuItemId . '&page=' . ($page - 1) . '&limit=' . $limit); ?>"
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
                        href="<?php echo Route::_('index.php?Itemid=' . $menuItemId . '&page=' . $i . '&limit=' . $limit); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Link alla pagina successiva -->
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?Itemid=' . $menuItemId . '&page=' . ($page + 1) . '&limit=' . $limit); ?>"
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
                        href="<?php echo Route::_('index.php?Itemid=' . $menuItemId . '&page=' . $totalPages . '&limit=' . $limit); ?>">
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





<div class="text-center mt-5"> <a href="<?php echo Uri::base(); ?>" class="btn btn-primary btn-sm">Crea Nuova</a> </div>
<?php
// Gestione della richiesta POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    if (isset($_POST['visualizza'])) {
        // Ottieni l'URL della voce di menu con ID 110
        $menuItem = $menu->getItem(110);
        if ($menuItem) {
            $url = Route::_('index.php?Itemid=' . (int) $menuItem->id . '&id=' . $id);
            // Reindirizza alla pagina
            header("Location: " . $url);
            exit; // Assicurati di uscire dopo il reindirizzamento
        }
    } elseif (isset($_POST['duplica'])) {
        // Ottieni l'oggetto database di Joomla
        $db = Factory::getDbo();
        // Crea la query per selezionare i dati dalla tabella 'competizione' con l'ID specificato
        $query = $db->getQuery(true)
            ->select('*')  // Seleziona tutte le colonne
            ->from($db->quoteName('#__competizioni'))  // Nome della tabella (assicurati che sia corretta)
            ->where($db->quoteName('id') . ' = ' . (int) $id);  // Condizione per l'ID
        // Esegui la query
        $db->setQuery($query);
        try {
            // Ottieni il risultato come un oggetto
            $result = $db->loadObject();
            // Se i dati sono trovati, mostra i risultati (oppure fai altro con i dati)
            if ($result) {
                if ($result->modalita !== 68){
                    $squadre = json_decode($result->squadre);
                    $nome_competizione = $result->nome_competizione;
                    $partecipanti = $result->partecipanti;
                } else {
                    $squadre = Competizione::getArticlesFromCategory($result->tipo, $userId);
                    $squadre = array_map(fn($squadra) => (string) $squadra->id, $squadre);
                    $nome_competizione = Competizione::getCategoryNameById($result->tipo)." - Simulazione";
                    $partecipanti = count($squadre);
                }
                //$squadre = json_encode($squadre);
                $data = array(
                    'user_id' => $result->user_id, // ID dell'utente
                    'nome_competizione' => $nome_competizione, // Nome della competizione
                    'modalita' => $result->modalita, // Modalità
                    'tipo' => $result->tipo,
                    'gironi' => $result->gironi, // Numero di gironi
                    'squadre' => $squadre, // ID delle squadre
                    'andata_ritorno' => $result->andata_ritorno, // Modalità andata/ritorno
                    'partecipanti' => $partecipanti, // Numero di partecipanti
                    'fase_finale' => $result->fase_finale, // Stato fase finale
                    'finita' => 0, // Stato finita
                );
                Competizione::insertCompetizione($data);
            }
        } catch (Exception $e) {
            // Gestisci gli errori
            echo 'Errore nel recupero dei dati: ' . $e->getMessage();
        }

        // Ricarica la pagina
        header("Location: " . $baseUrl . "index.php/competizioni-in-corso");
        exit;

    } elseif (isset($_POST['elimina'])) {
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $tablePartite = Competizione::getTablePartite($id);
        $tableStatistiche = Competizione::getTableStatistiche($id);

        // Inizia una transazione per garantire che tutte le eliminazioni siano atomiche
        $db->transactionStart();

        try {
            // Elimina la competizione dalla tabella principale
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__competizioni'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($id));
            $db->setQuery($query);
            $db->execute();

            // Esegui la query per eliminare la tabella delle partite
            $dropPartiteQuery = "DROP TABLE IF EXISTS " . $db->quoteName($tablePartite);
            $db->setQuery($dropPartiteQuery);
            $db->execute();

            // Esegui la query per eliminare la tabella delle statistiche
            $dropStatisticheQuery = "DROP TABLE IF EXISTS " . $db->quoteName($tableStatistiche);
            $db->setQuery($dropStatisticheQuery);
            $db->execute();

            // Conferma la transazione
            $db->transactionCommit();

            // Ricarica la pagina
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
            exit;
        } catch (Exception $e) {
            // Annulla la transazione in caso di errore
            $db->transactionRollback();
            echo "Errore durante l'eliminazione: " . htmlspecialchars($e->getMessage());
        }
    }
}

?>