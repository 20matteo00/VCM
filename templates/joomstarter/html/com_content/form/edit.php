<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Content\Site\View\Form\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_content.form-edit');

$this->tab_name = 'com-content-form';
$this->ignore_fieldsets = ['image-intro', 'image-full', 'jmetadata', 'item_associations'];
$this->useCoreUI = true;

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings
if (!$params->exists('show_publishing_options')) {
    $params->set('show_urls_images_frontend', '0');
}
?>
<div class="edit item-page text-center justify-content-center d-flex">
    <?php if ($params->get('show_page_heading')): ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_content&a_id=' . (int) $this->item->id); ?>" method="post"
        name="adminForm" id="adminForm" class="form-validate form-vertical mioeditform">
        <fieldset>
            <?php echo HTMLHelper::_('uitab.startTabSet', $this->tab_name, ['active' => 'editor', 'recall' => true, 'breakpoint' => 768]); ?>

            <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'editor', Text::_('COM_CONTENT_ARTICLE_CONTENT')); ?>

            <?php echo $this->form->renderField('title'); ?>


            <?php if (is_null($this->item->id)): ?>
                <?php echo $this->form->renderField('alias'); ?>
            <?php endif; ?>

            <?php echo $this->form->renderField('articletext'); ?>

            <?php if ($this->captchaEnabled): ?>
                <?php echo $this->form->renderField('captcha'); ?>
            <?php endif; ?>
            <div id="publishing">
                <?php echo $this->form->renderField('state'); ?>
                <?php echo $this->form->renderField('catid'); ?>
                <?php echo $this->form->renderField('tags'); ?>
            </div>

            <?php echo HTMLHelper::_('uitab.endTab'); ?>


            <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

            <?php if (Multilanguage::isEnabled()): ?>
                <?php echo HTMLHelper::_('uitab.addTab', $this->tab_name, 'language', Text::_('JFIELD_LANGUAGE_LABEL')); ?>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo HTMLHelper::_('uitab.endTab'); ?>
            <?php else: ?>
                <?php echo $this->form->renderField('language'); ?>
            <?php endif; ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
            <?php $url = Route::_('index.php?option=com_content&view=category&id=' . (int) $params['catid']); ?>

            <input type="hidden" name="task" value="">
            <input type="hidden" name="return" value="<?php echo $this->return_page; ?>">
            <?php echo HTMLHelper::_('form.token'); ?>
        </fieldset>
        <div class="d-grid gap-2 d-sm-block my-2">
            <button type="button" class="btn btn-primary" data-submit-task="article.save">
                <span class="icon-check" aria-hidden="true"></span>
                <?php echo Text::_('JSAVEANDCLOSE'); ?>
            </button>
            <button type="button" class="btn btn-danger" data-submit-task="article.cancel">
                <span class="icon-times" aria-hidden="true"></span>
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </form>
</div>