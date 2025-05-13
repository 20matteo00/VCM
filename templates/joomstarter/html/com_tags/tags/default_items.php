<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Tags\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Tags\Site\View\Tags\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_tags.tags-default');

// Get the user object.
$user = $this->getCurrentUser();

// Check if user is allowed to add/edit based on tags permissions.
$canEdit      = $user->authorise('core.edit', 'com_tags');
$canCreate    = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');

$columns = $this->params->get('tag_columns', 1);

// Avoid division by 0 and negative columns.
if ($columns < 1) {
    $columns = 1;
}

$bsspans = floor(12 / $columns);

if ($bsspans < 1) {
    $bsspans = 1;
}

$bscolumns = min($columns, floor(12 / $bsspans));
$n         = count($this->items);
?>

<div class="com-tags__items">
<?php if ($this->items == false || $n === 0) : ?>
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
        <?php echo Text::_('COM_TAGS_NO_TAGS'); ?>
    </div>
<?php else : ?>
    <div class="container">
        <div class="row">
            <?php foreach ($this->items as $i => $item) : ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 <?php echo (!empty($item->access) && in_array($item->access, $this->user->getAuthorisedViewLevels())) ? 'border-info' : 'border-danger'; ?> text-center">
                        <div class="card-body">
                            <h3 class="card-title mb-0 h-100 d-flex align-items-center">
                                <?php if ((!empty($item->access)) && in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
                                    <a href="<?php echo Route::_(RouteHelper::getComponentTagRoute($item->id . ':' . $item->alias, $item->language)); ?>" class="text-decoration-none text-primary d-block w-100">
                                        <?php echo $this->escape($item->title); ?>
                                    </a>
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

</div>
