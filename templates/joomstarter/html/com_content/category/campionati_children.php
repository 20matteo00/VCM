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
use Joomstarter\Helpers\Competizione;


/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$lang   = $this->getLanguage();
$user   = $this->getCurrentUser();
$groups = $user->getAuthorisedViewLevels();
?>
<?/*
<div class="table-responsive category-table-container">
    <table class="table table-striped category-table">
        <thead>
            <tr>
                <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                <th class="category-header-title"><?php echo Text::_('CAMPIONATO'); ?></th>
                <th class="category-header-participants"><?php echo Text::_('PARTECIPANTI'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
                <?php if (in_array($child->access, $groups)) : ?>
                    <tr>
                        <td class="category-image-cell">
                            <?php if ($child->getParams()->get('image')) : ?>
                                <img src="<?php echo htmlspecialchars($child->getParams()->get('image')); ?>" alt="<?php echo $this->escape($child->title); ?>" class="category-image">
                            <?php endif; ?>
                        </td>
                        <td class="category-title-cell">
                            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>" class="category-title">
                                <?php echo $this->escape($child->title); ?>
                            </a>
                        </td>
                        <td class="category-items-cell">
                            <span class="badge bg-info category-badge" title="<?php echo HTMLHelper::_('tooltipText', 'JOOM_NUM_ITEMS'); ?>">
                                <?php echo $child->getNumItems(true); ?>
                            </span>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
*/?>