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
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;
// Utilizzo della funzione
$categoryId = 8; // ID della categoria principale

// Ottieni il numero totale di articoli per la paginazione
$total = Competizione::getTotalArticlesFromSubcategories($categoryId, $userId);

// Ottieni il valore di `limit` dalla richiesta GET o usa il valore di default
$app = Factory::getApplication();
$limit = $app->input->getInt('limit', 20);

// Inizio della pagina corrente
$limitstart = $app->input->getInt('limitstart', 0);

// Ottieni gli articoli con paginazione
$articles = Competizione::getArticlesFromSubcategoriesPagination($categoryId, $userId, $limit, $limitstart);

// Creiamo la paginazione
$pagination = new Joomla\CMS\Pagination\Pagination($total, $limitstart, $limit);
$pagination->setAdditionalUrlParam('limit', $limit); // Aggiungi `limit` come parametro nella query string


// Controlla se ci sono articoli
if ($articles):
    ?>
    <form action="" method="get">
        <div class="form-group w-25 mx-auto mb-3">
            <label for="limit"><?php echo Text::_('Seleziona il numero di articoli per pagina'); ?></label>
            <select name="limit" id="limit" class="form-control" onchange="this.form.submit()">
                <option value="0" <?php echo $limit == 0 ? 'selected' : ''; ?>>Tutto</option>
                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                <option value="30" <?php echo $limit == 30 ? 'selected' : ''; ?>>30</option>
                <option value="40" <?php echo $limit == 40 ? 'selected' : ''; ?>>40</option>
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
            </select>
        </div>
    </form>
    <div class="table-responsive category-table-container">
        <table class="table table-striped category-table">
            <thead>
                <tr>
                    <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                    <th class="category-header-title"><?php echo Text::_('SQUADRA'); ?></th>
                    <th class="category-header-force"><?php echo Text::_('FORZA'); ?></th>
                    <th class="category-header-participants"><?php echo Text::_('CAMPIONATO'); ?></th>
                </tr>
            </thead>
            <tbody class="allarticles">
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td class="category-items-cell">
                            <?php
                            // Ottieni l'immagine dell'articolo
                            $images = json_decode($article->images);
                            $imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : '/images/default.webp';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>"
                                alt="<?php echo htmlspecialchars($article->title); ?>" class="category-image">
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
                        <td class="category-items-cell">
                            <?php echo htmlspecialchars($article->number_value); ?>
                        </td>
                        <td class="category-items-cell">
                            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($article->catid)); ?>">                                
                                <img src="<?php echo json_decode($article->category_params)->image; ?>" alt="<?php $article->category_title; ?>">
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p><?php echo Text::_('No articles found'); ?></p>
<?php endif; ?>

<!-- Paginazione centrata con mx-auto -->
<div class="pagination justify-content-center">
    <?php echo $pagination->getPagesLinks(); ?>
</div>

<div class="text-center mt-5"> <a href="<?php echo Uri::base(); ?>index.php/crea-squadra" class="btn btn-success btn-sm">Crea Nuova Squadra</a> </div>
