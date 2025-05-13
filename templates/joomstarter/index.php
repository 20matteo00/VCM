<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.JoomStarter
 *
 * @copyright   (C) YEAR Your Name
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * This is a heavily stripped down/modified version of the default Cassiopeia template, designed to build new templates off of.
 */

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
/** @var Joomla\CMS\Document\HtmlDocument $this */

$app = Factory::getApplication();
$wa = $this->getWebAssetManager();

$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'icon', 'rel', ['type' => 'image/x-icon']);


// Detecting Active Variables
$option = $app->input->getCmd('option', '');
$view = $app->input->getCmd('view', '');
$layout = $app->input->getCmd('layout', '');
$task = $app->input->getCmd('task', '');
$itemid = $app->input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

$testparam = $this->params->get('testparam');

$templatePath = 'templates/' . $this->template;

HTMLHelper::_('bootstrap.collapse');
HTMLHelper::_('bootstrap.dropdown');



$wa->useStyle('template.joomstarter.mainstyles');
$wa->useStyle('template.joomstarter.user');
$wa->useScript('template.joomstarter.scripts');
/* $wa->useStyle('jsdelivr.css');
$wa->useScript('jsdelivr.js');
$wa->useScript('cloudflare.js'); */

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
    <script>!function (d, l, e, s, c) { e = d.createElement("script"); e.src = "//ad.altervista.org/js.ad/size=300X250/?ref=" + encodeURIComponent(l.hostname + l.pathname) + "&r=" + Date.now(); s = d.scripts; c = d.currentScript || s[s.length - 1]; c.parentNode.insertBefore(e, c) }(document, location)</script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.umd.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="site <?php echo $pageclass; ?>" data-bs-theme="light">
    <header class="sticky-header mb-3">

        <nav class="navbar navbar-dark bg-dark navbar-expand-xxl">
            <div class="container">
                <a href="<?php echo $this->baseurl; ?>" class="navbar-brand"
                    alt="<?php echo htmlspecialchars($sitename, ENT_QUOTES, 'UTF-8'); ?>">
                    <img src="<?php echo $this->baseurl; ?>/images/logo.png"
                        alt="<?php echo htmlspecialchars($sitename, ENT_QUOTES, 'UTF-8'); ?>" />
                </a>
                <button class="mx-3 my-2 navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainmenu" aria-controls="mainmenu" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <?php if ($this->countModules('menu')): ?>
                    <div class="mx-3 my-2 collapse navbar-collapse" id="mainmenu">
                        <jdoc:include type="modules" name="menu" style="none" />
                    </div>

                <?php endif; ?>
                <div class="mx-3 my-2 text-white fw-bold h5 m-0">
                    <?php

                    $user = Factory::getUser();
                    $username = $user->username;
                    if ($username != "")
                        echo 'Benvenuto <a class="text-decoration-none text-white" href="' . Route::_('index.php?option=com_users&view=profile&id=' . $user->id) . '">' . $username . '</a>';
                    ?>
                </div>

            </div>
        </nav>

        <?php if ($this->countModules('header')): ?>
            <div class="headerClasses">
                <jdoc:include type="modules" name="header" style="none" />
            </div>
        <?php endif; ?>
    </header>
    <div class="siteBody mb-5" style="min-height: 300px;">
        <div class="container">

            <?php if ($this->countModules('breadcrumbs')): ?>
                <div class="breadcrumbs">
                    <jdoc:include type="modules" name="breadcrumbs" style="none" />
                </div>
            <?php endif; ?>
            <?php if ($this->countModules('custom')): ?>
                <div class="container">
                    <jdoc:include type="modules" name="custom" style="none" />
                </div>
            <?php endif; ?>
            <div class="row">

                <?php if ($this->countModules('sidebar')): ?>
                    <div class="col-xs-12 col-lg-8">

                        <main>

                            <jdoc:include type="message" />

                            <jdoc:include type="component" />
                        </main>
                    </div>

                    <div class="col-xs-12 col-lg-4">

                        <jdoc:include type="modules" name="sidebar" style="superBasicMod" />
                    </div>

                <?php else: ?>

                    <main>
                        <jdoc:include type="component" />
                    </main>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($this->countModules('footer')): ?>
        <footer class="footer mt-auto py-3 bg-dark ">
            <div class="container">

                <jdoc:include type="modules" name="footer" style="none" />
            </div>
        </footer>
    <?php endif; ?>

    <jdoc:include type="modules" name="debug" style="none" />
</body>

</html>