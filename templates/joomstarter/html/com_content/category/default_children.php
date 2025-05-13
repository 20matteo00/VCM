<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;
use Joomstarter\Helpers\Competizione;

// Creiamo un'istanza del database
$db = Factory::getDbo();

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$lang = $this->getLanguage();
$user = $this->getCurrentUser();
$groups = $user->getAuthorisedViewLevels();
$userId = $user->id;
$total = count($this->children[$this->category->id]);
// Inizia la sessione
$session = Factory::getSession();
// Ottieni il valore di limit dalla richiesta GET o dalla sessione
$limit = Factory::getApplication()->input->getInt('limit', $session->get('limit', 10));

// Salva il valore di limit nella sessione
$session->set('limit', $limit);

$limitstart = Factory::getApplication()->input->getInt('limitstart', 0);

// Filtra gli articoli in base al limite
if ($limit == 0) {
    $items = $this->children[$this->category->id];  // Tutti gli articoli
} else {
    $items = array_slice($this->children[$this->category->id], $limitstart, $limit);
}

// Creiamo la paginazione
$pagination = new Joomla\CMS\Pagination\Pagination($total, $limitstart, $limit);

?>
<form action="" method="get">
    <div class="form-group w-25 mx-auto mb-3">
        <label for="limit"><?php echo Text::_('Seleziona il numero di articoli per pagina'); ?></label>
        <select name="limit" id="limit" class="form-control" onchange="this.form.submit()">
            <option value="0" <?php echo $limit == 0 ? 'selected' : ''; ?>>Tutto</option>
            <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
            <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
            <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
            <option value="30" <?php echo $limit == 30 ? 'selected' : ''; ?>>30</option>
        </select>
    </div>
</form>
<div class="table-responsive category-table-container">
    <table class="table table-striped category-table">
        <thead>
            <tr>
                <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                <th class="category-header-title"><?php echo Text::_('CAMPIONATO'); ?></th>
                <th class="category-header-participants"><?php echo Text::_('PARTECIPANTI'); ?></th>
                <th class="category-header-participants"><?php echo Text::_('STATO'); ?></th>
                <th class="category-header-participants"><?php echo Text::_('AZIONI'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $id => $child): ?>
                <?php if (in_array($child->access, $groups)): ?>
                    <tr>
                        <td class="category-image-cell">
                            <?php if ($child->getParams()->get('image')): ?>
                                <img src="<?php echo htmlspecialchars($child->getParams()->get('image')); ?>"
                                    alt="<?php echo $this->escape($child->title); ?>" class="category-image">
                            <?php endif; ?>
                        </td>
                        <td class="category-title-cell">
                            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>"
                                class="category-title h4">
                                <?php echo $this->escape($child->title); ?>
                            </a>
                        </td>
                        <td class="category-items-cell">
                            <span class="badge bg-info category-badge"
                                title="<?php echo HTMLHelper::_('tooltipText', 'JOOM_NUM_ITEMS'); ?>">
                                <?php
                                $partecipants = count(Competizione::getArticlesFromCategory($child->id, $userId));
                                echo $partecipants;
                                ?>
                            </span>
                        </td>
                        <td class="category-items-cell">
                            <?php
                            // Assicurati di avere accesso all'oggetto della categoria
                            $categoryId = (int) $child->id;
                            $categoryTitle = Competizione::getCategoryTitleById($categoryId);
                            // Creazione della query per ottenere i tag associati alla categoria
                            $query = $db->getQuery(true)
                                ->select('*') // Seleziona tutte le colonne dalla mappa dei contenuti e tag
                                ->from($db->quoteName('vcmdb_contentitem_tag_map'))
                                ->where($db->quoteName('content_item_id') . ' = ' . (int) $categoryId); // Confronta con content_item_id
                    
                            // Esegui la query
                            $db->setQuery($query);
                            $tagMappings = $db->loadObjectList();

                            // Controlla se ci sono tag disponibili
                            if (!empty($tagMappings)) {
                                foreach ($tagMappings as $tagMapping) {
                                    // Ora recuperiamo il titolo del tag usando il tag_id
                                    $tagId = (int) $tagMapping->tag_id;

                                    // Creazione della query per ottenere il nome del tag
                                    $tagQuery = $db->getQuery(true)
                                        ->select($db->quoteName('t.title', 'tag_title'))
                                        ->from($db->quoteName('#__tags', 't'))
                                        ->where($db->quoteName('t.id') . ' = ' . $tagId);

                                    // Esegui la query per ottenere il titolo del tag
                                    $db->setQuery($tagQuery);
                                    $tag = $db->loadObject();

                                    // Controlla se il tag esiste
                                    if ($tag) {
                                        // Stampa il nome del tag come link cliccabile
                                        echo '<a href="' . Route::_('index.php?option=com_tags&view=tag&id=' . $tagId) . '" class="badge bg-warning category-badge">' . $this->escape($tag->tag_title) . '</a>';
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td class="category-title-cell">
                            <!-- Form per simulare il campionato -->
                            <form action="" method="post" class="text-center d-flex justify-content-evenly">
                                <input type="hidden" value="<?php echo $categoryId; ?>" name="catid">
                                <input type="hidden" value="<?php echo $categoryTitle; ?>" name="cattitle">
                                <?php if ($partecipants >= 4 && $partecipants <= 24): ?>
                                    <button type="submit" class="btn btn-success my-2" name="simula_campionato">Simula</button>
                                    <?php if ($categoryId !== 71): ?>
                                        <button type="submit" class="btn btn-info my-2" name="albo">Albo</button>
                                        <button type="submit" class="btn btn-warning my-2" name="perpetua">Perpetua</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Paginazione centrata con mx-auto -->
<div class="pagination justify-content-center">
    <?php echo $pagination->getPagesLinks(); ?>
</div>

<?php
// Gestione della simulazione del campionato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catid = $_POST['catid'];
    $categoryTitle = $_POST['cattitle'];
    if (isset($_POST['simula_campionato'])) {
        $squadre = Competizione::getArticlesFromCategory($catid, $userId);
        $partecipanti = count($squadre);
        if ($partecipanti < 4 || $partecipanti > 24)
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
        header("Location: " . $baseUrl . "competizioni-in-corso");
        exit;
    } elseif (isset($_POST['albo'])) {
        header("Location: " . $baseUrl . "albo-doro?catid=" . $catid);
        exit;
    } elseif (isset($_POST['perpetua'])) {
        header("Location: " . $baseUrl . "classifica-perpetua?catid=" . $catid);
        exit;
    }
}
?>