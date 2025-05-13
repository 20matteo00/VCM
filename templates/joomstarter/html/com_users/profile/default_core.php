<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Users\Site\View\Profile\HtmlView $this */
?>
<fieldset id="users-profile-core" class="com-users-profile__core">
    <legend class="fs-3 text-primary mb-4">
        <?php echo Text::_('COM_USERS_PROFILE_CORE_LEGEND'); ?>
    </legend>

    <div class="mb-3">
        <h4 class="text-secondary"><?php echo Text::_('COM_USERS_PROFILE_NAME_LABEL'); ?></h4>
        <p class="fw-bold"><?php echo $this->escape($this->data->name); ?></p>
    </div>

    <div class="mb-3">
        <h4 class="text-secondary"><?php echo Text::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?></h4>
        <p class="fw-bold"><?php echo $this->escape($this->data->username); ?></p>
    </div>

    <div class="mb-3">
        <h4 class="text-secondary"><?php echo Text::_('COM_USERS_PROFILE_EMAIL_LABEL'); ?></h4>
        <p class="fw-bold"><?php echo $this->escape($this->data->email); ?></p>
    </div>

    <div class="mb-3">
        <h4 class="text-secondary"><?php echo Text::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?></h4>
        <p class="fw-bold">
            <?php echo HTMLHelper::_('date', $this->data->registerDate, Text::_('DATE_FORMAT_LC1')); ?>
        </p>
    </div>

    <div class="mb-3">
        <h4 class="text-secondary"><?php echo Text::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?></h4>
        <p class="fw-bold">
            <?php if ($this->data->lastvisitDate !== null) : ?>
                <?php echo HTMLHelper::_('date', $this->data->lastvisitDate, Text::_('DATE_FORMAT_LC1')); ?>
            <?php else : ?>
                <?php echo Text::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
            <?php endif; ?>
        </p>
    </div>
</fieldset>
