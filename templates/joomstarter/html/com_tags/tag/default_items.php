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

/** @var \Joomla\Component\Tags\Site\View\Tag\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_tags.tag-default');

// Get the user object.
$user = $this->getCurrentUser();

// Check if user is allowed to add/edit based on tags permissions.
// Do we really have to make it so people can see unpublished tags???
$canEdit      = $user->authorise('core.edit', 'com_tags');
$canCreate    = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');
?>
<div class="com-tags__items">
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
            <?php echo Text::_('COM_TAGS_NO_ITEMS'); ?>
        </div>
    <?php else : ?>
        <div class="container">
            <div class="row">
                <?php foreach ($this->items as $i => $item) : ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 <?php echo $item->core_state == 0 ? 'border-danger' : 'border-info'; ?> text-center">
                            <div class="card-body">
                                <!-- Title Section -->
                                <h3 class="card-title mb-0 h-100 d-flex align-items-center">
                                    <?php if (($item->type_alias === 'com_users.category') || ($item->type_alias === 'com_banners.category')) : ?>
                                        <?php echo $this->escape($item->core_title); ?>
                                    <?php else : ?>
                                        <a href="<?php echo Route::_($item->link); ?>" class="text-decoration-none text-primary d-block w-100">
                                            <?php echo $this->escape($item->core_title); ?>
                                        </a>
                                    <?php endif; ?>
                                </h3>

                                <!-- Additional Content -->
                                <?php echo $item->event->afterDisplayTitle; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    <?php endif; ?>
</div>