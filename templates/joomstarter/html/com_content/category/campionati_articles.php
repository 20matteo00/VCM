<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomstarter\Helpers\Competizione;
use Joomla\CMS\Uri\Uri;
$baseUrl = Uri::base();

// Ottieni l'ID dell'utente corrente e la categoria
$user = Factory::getUser();
$userId = $user->id;
$categoryId = $this->category->id;
$groups = $user->get('groups');
$isadmin = in_array(8, $groups);
// Ottieni tutti gli articoli della categoria
$allArticles = Competizione::getArticlesFromCategory($categoryId, $userId);
$total = count($allArticles);
// Recupera il valore di `limit` dalla richiesta GET o imposta un valore di default
$app = Factory::getApplication();
$limit = $app->input->getInt('limit', 5);

// Ottieni la pagina corrente dal parametro GET
$page = $app->input->getInt('page', 1);

if ($limit === 0)
    $limit = $total;

// Calcola il numero totale di pagine
$totalPages = ceil($total / $limit);

// Calcola l'indice di partenza per gli articoli della pagina corrente
$startIndex = ($page - 1) * $limit;
$articles = array_slice($allArticles, $startIndex, $limit);

// Ottieni il titolo della categoria e URL di modifica
$categoryTitle = Competizione::getCategoryTitleById($categoryId);
$modificasquadra = Competizione::getUrlMenu(112);

// Verifica se il titolo è stato recuperato correttamente
if ($categoryTitle) {
    echo "<p class='text-center m-0 h1 fw-bold'>" . $categoryTitle . "</p>";
}

?>

<?php if (!empty($articles)): ?>
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
    <div class="table-responsive category-table-container">
        <p class="text-center"></p>
        <table class="table table-striped category-table">
            <thead>
                <tr>
                    <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                    <th class="category-header-title"><?php echo Text::_('SQUADRA'); ?></th>
                    <th class="category-header-force"><?php echo Text::_('FORZA'); ?></th>
                    <?php if ($isadmin || $categoryId === 71): ?>
                        <th class="category-header-logo"><?php echo Text::_('AZIONI'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="allarticles">
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td class="category-items-cell">
                            <?php
                            $images = json_decode($article->images);
                            $imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : '/images/default.webp';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>"
                                aria-alt="<?php echo htmlspecialchars($article->title); ?>" class="category-image">
                        </td>
                        <td class="category-title-cell">
                            <div class="squadra" style="background-color:<?php echo htmlspecialchars($article->color1); ?>;">
                                <a href="<?php echo Route::_("index.php?option=com_content&view=article&id={$article->id}&catid={$article->catid}"); ?>"
                                    class="category-title w-100 d-block"
                                    style="color:<?php echo htmlspecialchars($article->color2); ?>;">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </div>
                        </td>
                        <td class="category-items-cell"><?php echo htmlspecialchars($article->forza); ?></td>
                        <?php if ($isadmin || $categoryId === 71): ?>

                            <td class="category-items-cell">
                                <form class="my-1" action="<?php echo $baseUrl; ?>index.php/modifica-squadra" method="get">
                                    <input type="hidden" value="<?php echo $article->id; ?>" name="id">
                                    <input type="hidden" value="<?php echo $categoryId; ?>" name="catid">
                                    <input type="hidden" value="<?php echo $userId; ?>" name="user">
                                    <button type="submit" class="btn btn-warning btn-sm my-1" name="modifica"
                                        value="modifica">Modifica</button>
                                    <button type="submit" class="btn btn-danger btn-sm my-1" name="elimina"
                                        value="elimina">Elimina</button>
                                </form>
                            </td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginazione -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Link alla prima pagina -->
                <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=1&limit=' . $limit); ?>"><span
                            class="icon-angle-double-left" aria-hidden="true"></span></a>
                </li>

                <!-- Link alla pagina precedente -->
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . ($page - 1) . '&limit=' . $limit); ?>"
                            aria-label="Precedente">
                            <span class="icon-angle-left" aria-hidden="true"></span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link"><span class="icon-angle-left"
                                aria-hidden="true"></span></span></li>
                <?php endif; ?>

                <!-- Link pagine centrali -->
                <?php
                $start = max(1, $page - 5);
                $end = min($totalPages, $page + 5);

                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . $i . '&limit=' . $limit); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Link alla pagina successiva -->
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . ($page + 1) . '&limit=' . $limit); ?>"
                            aria-label="Successiva">
                            <span class="icon-angle-right" aria-hidden="true"></span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link"><span class="icon-angle-right"
                                aria-hidden="true"></span></span></li>
                <?php endif; ?>

                <!-- Link all'ultima pagina -->
                <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . $totalPages . '&limit=' . $limit); ?>"><span
                            class="icon-angle-double-right" aria-hidden="true"></span></a>
                </li>
            </ul>
        </nav>

    <?php endif; ?>
<?php endif; ?>

<?php if ($categoryId === 71): ?>
    <div class="text-center my-5"> <a href="<?php echo Uri::base(); ?>index.php/crea-squadra"
            class="btn btn-success btn-sm">Crea Nuova Squadra</a> </div>
<?php endif; ?>

<?php if ($total >= 4 && $total <= 24): ?>
<!-- Form per simulare il campionato -->
<form action="" method="post" class="text-center">
    <input type="hidden" value="<?php echo $categoryId; ?>" name="catid">
    <button type="submit" class="btn btn-success btn" name="simula_campionato">Simula Campionato</button>
    <?php if ($categoryId !== 71): ?>
        <button type="submit" class="btn btn-info btn" name="albo">Albo</button>
        <button type="submit" class="btn btn-warning btn" name="perpetua">Perpetua</button>
    <?php endif; ?>
</form>
<?php endif; ?>

<?php
// Gestione della simulazione del campionato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catid = $_POST['catid'];
    if (isset($_POST['simula_campionato'])) {
        $squadre = Competizione::getArticlesFromCategory($catid, $userId);
        $partecipanti = count($squadre);
        if ($partecipanti < 4 || $partecipanti > 24 || $partecipanti % 2 != 0)
            return;
        $squad = array_map(fn($squadra) => (string) $squadra->id, $squadre);
        $data = array(
            'user_id' => $userId,
            'nome_competizione' => $categoryTitle . " - Simulazione",
            'modalita' => 68,
            'tipo' => $catid,
            'gironi' => 0,
            'squadre' => $squad,
            'andata_ritorno' => 1,
            'partecipanti' => $partecipanti,
            'fase_finale' => 0,
            'finita' => 0,
        );
        Competizione::insertCompetizione($data);
        header("Location: " . $baseUrl . "index.php/competizioni-in-corso");
        exit;
    } elseif (isset($_POST['albo'])) {
        header("Location: " . $baseUrl . "index.php/albo-doro?catid=" . $catid);
        exit;
    } elseif (isset($_POST['perpetua'])) {
        header("Location: " . $baseUrl . "index.php/classifica-perpetua?catid=" . $catid);
        exit;
    }
}
?>