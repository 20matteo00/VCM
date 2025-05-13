<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
// Creiamo un'istanza del database
$db = Factory::getDbo();

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$lang = $this->getLanguage();
$user = $this->getCurrentUser();
$groups = $user->getAuthorisedViewLevels();
// Ottieni il Base URL dinamicamente
$baseUrl = URI::base();

?>

<div class="table-responsive category-table-container">
    <div class="container mt-5 w-25 text-center" style="min-width: 250px;">
        <h2 class="text-center fw-bold">Predefiniti:</h2>
        <div class="row">
            <div class="col-12 mb-2">
                <div class="card creacomp">
                    <div class="card-body text-center ">
                        <a href="<?php echo $baseUrl; ?>index.php/campionati">Campionati</a>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="text-center fw-bold">Personalizzati:</h2>
        <div class="row">
            <?php foreach ($this->children[$this->category->id] as $id => $child): ?>
                <?php if (in_array($child->access, $groups)): ?>
                    <div class="col-12 mb-2">
                        <div class="card creacomp">
                            <div class="card-body text-center ">
                                <a
                                    href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $child->id); ?>">
                                    <?php echo htmlspecialchars($child->title); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>