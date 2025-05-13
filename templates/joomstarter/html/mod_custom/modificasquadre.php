<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver; // Aggiungi per usare il DB
use Joomstarter\Helpers\Competizione;

// Ottieni l'ID dell'utente corrente
$baseUrl = Uri::base();
$user = Factory::getUser();
$userId = $user->id;
$groups = $user->get('groups');
$isadmin = in_array(8, $groups);

if (isset($_GET['modifica'])) {
    isset($_GET['id']) ? $idsquadra = $_GET['id'] : $idsquadra = null;
    isset($_GET['catid']) ? $squadracat = $_GET['catid'] : $squadracat = null;

    if ($idsquadra !== null) {
        $cf = Competizione::getCustomFields($idsquadra);
        $colors = !empty($cf[1]) ? $cf[1]->value : '#000000';
        $colort = !empty($cf[2]) ? $cf[2]->value : '#ffffff';
        $forza = !empty($cf[3]) ? $cf[3]->value : 0;
        // Ottieni il titolo dell'articolo
        $articleTitle = Competizione::getArticleTitleById($idsquadra);
    }
    if (!$isadmin && $squadracat != 71)
        header("location: " . $baseUrl);
} elseif (isset($_GET['elimina'])) {
    isset($_GET['user']) ? $user = $_GET['user'] : $user = null;
    isset($_GET['catid']) ? $squadracat = $_GET['catid'] : $squadracat = null;
    isset($_GET['id']) ? $idsquadra = $_GET['id'] : $idsquadra = null;
    Competizione::deleteArticleById($idsquadra, $user);

    // Reindirizza all'articolo
    header('Location: ' . Route::_('index.php?option=com_content&view=article&id=' . $idsquadra . '&catid=' . $squadracat, false));
    exit();
}
?>
<form action="" method="post" class="container mt-4">
    <fieldset>
        <legend class="text-center mb-4">Modifica Dati <?php echo Competizione::getArticleTitleById($idsquadra); ?>
        </legend>

        <div class="form-group row align-items-end">
            <!-- Titolo -->
            <div class="col-md-3">
                <label for="title" class="col-form-label">Titolo Articolo</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($articleTitle); ?>"
                    class="form-control">
            </div>

            <!-- Forza -->
            <div class="col-md-2">
                <label for="forza" class="col-form-label">Forza</label>
                <input type="number" name="forza" id="forza" value="<?php echo htmlspecialchars($forza); ?>"
                    class="form-control">
            </div>

            <!-- Colore Sfondo -->
            <div class="col-md-2">
                <label for="color1" class="col-form-label">Colore Sfondo</label>
                <input type="color" name="color1" id="color1" value="<?php echo htmlspecialchars($colors); ?>"
                    class="form-control">
            </div>

            <!-- Colore Testo -->
            <div class="col-md-2">
                <label for="color2" class="col-form-label">Colore Testo</label>
                <input type="color" name="color2" id="color2" value="<?php echo htmlspecialchars($colort); ?>"
                    class="form-control">
            </div>

            <!-- Salva -->
            <div class="col-md-3 text-center">
                <button type="submit" name="save" class="btn btn-success btn-lg w-100 mt-3">Salva</button>
            </div>
        </div>
    </fieldset>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    // Gestisci il salvataggio dei dati
    $color1 = $_POST['color1'];
    $color2 = $_POST['color2'];
    $forza = $_POST['forza'];
    $newTitle = $_POST['title']; // Nuovo titolo dell'articolo

    // Aggiorna i campi personalizzati
    Competizione::setCustomFields($idsquadra, $color1, $color2, $forza);

    // Ottieni il database Joomla
    $db = Factory::getDbo();

    // Crea la query per aggiornare l'articolo
    $query = $db->getQuery(true)
                ->update($db->quoteName('#__content'))
                ->set($db->quoteName('title') . ' = ' . $db->quote($newTitle))
                ->set($db->quoteName('alias') . ' = ' . $db->quote($newTitle)) // Resetta l'alias
                ->where($db->quoteName('id') . ' = ' . (int) $idsquadra);

    // Esegui la query
    $db->setQuery($query);
    $db->execute();

    $caturl = Competizione::getCategoryUrlByArticleId($idsquadra);
    header("Location: " . $caturl);  // Reindirizza alla categoria
    exit;
}
?>
