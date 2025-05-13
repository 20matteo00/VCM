<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomstarter\Helpers\Competizione;
use Joomla\CMS\Router\Route;

// Ottieni l'ID della categoria attuale
$currentCategoryId = $this->category->id;
$user = Factory::getUser();
$userId = $user->id; // ID dell'utente corrente
if ($userId == 0)
    $userId = 988;



// Recupera le sottocategorie della categoria 8
$subcategoryIds = Competizione::getSubcategories(8);
// Recupera gli articoli delle sottocategorie
$articles = Competizione::getArticlesInSubcategories($subcategoryIds, $userId);
// Recupera i sottotag del tag 2
$subTags = Competizione::getSubTags(2);
// Modalità specifiche
$modalita = [68, 69, 70];

$campionati = Competizione::getSubcategories(8, true);

if (in_array($this->category->id, $modalita)): ?>
    <form action="#" method="post" id="form-participanti">

        <div class="container mt-5">
            <div class="row justify-content-center"> <!-- Centra il contenuto -->
                <div class="col-md-6">
                    <?php
                    // Determina i colori e il titolo della card
                    $cardClass = '';
                    $headerClass = '';

                    if ($this->category->id == 68) {
                        $cardClass = 'border-primary';
                        $headerClass = 'bg-primary text-white';
                    } elseif ($this->category->id == 69) {
                        $cardClass = 'border-success';
                        $headerClass = 'bg-success text-white';
                    } elseif ($this->category->id == 70) {
                        $cardClass = 'border-warning';
                        $headerClass = 'bg-warning text-dark';
                    }
                    ?>

                    <div class="card text-center <?= $cardClass; ?> mb-4">
                        <div class="card-header <?= $headerClass; ?>">
                            <h2><?= htmlspecialchars($this->category->title); ?></h2>
                        </div>
                        <div class="card-body">
                            <!-- Campo "Nome" -->
                            <div class="form-group">
                                <label for="nome_campionato">Nome:</label>
                                <input type="text" class="form-control" id="nome_campionato" name="nome_campionato"
                                    required="" placeholder="Competizione...">
                            </div>

                            <!-- Campo "Andata/Ritorno" -->
                            <div class="form-group">
                                <label for="andata_ritorno">Andata/Ritorno:</label>
                                <select class="form-control" id="andata_ritorno" name="andata_ritorno" required="">
                                    <option value="1">Sì</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- Campo "Partecipanti" in base all'ID categoria -->
                            <div class="form-group">
                                <?php if ($this->category->id == 68): ?>
                                    <label for="numero_partecipanti">Partecipanti:</label>
                                    <input type="number" class="form-control" id="numero_partecipanti"
                                        name="numero_partecipanti" min="4" max="24" required="" value="4">
                                <?php elseif ($this->category->id == 69): ?>
                                    <label for="numero_partecipanti">Partecipanti (esponenti di 2):</label>
                                    <select class="form-control" id="numero_partecipanti" name="numero_partecipanti"
                                        required="">
                                        <option value="4">4</option>
                                        <option value="8">8</option>
                                        <option value="16">16</option>
                                        <option value="32">32</option>
                                        <option value="64">64</option>
                                        <option value="128">128</option>
                                    </select>
                                <?php elseif ($this->category->id == 70): ?>
                                    <label for="gironi">Gironi:</label>
                                    <select class="form-control" id="gironi" name="gironi" required="">
                                        <option value="2">2</option>
                                        <option value="4">4</option>
                                        <option value="8">8</option>
                                    </select>

                                    <label for="numero_partecipanti">Partecipanti:</label>
                                    <select class="form-control" id="numero_partecipanti" name="numero_partecipanti"
                                        required=""></select>

                                    <label for="numero_partecipanti_fasefinale">Fase Finale:</label>
                                    <select class="form-control" id="numero_partecipanti_fasefinale" name="fase_finale"
                                        required=""></select>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary my-3" id="submit-button" name="submit-button"
                                disabled>Invia</button>
                        </div> <!-- Fine card body -->
                    </div> <!-- Fine card -->
                </div> <!-- Fine colonna -->
            </div> <!-- Fine row -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mt-4">
                        <label for="search">Cerca:</label>
                        <input type="text" id="search" name="search" class="form-control" style="height:116px;"
                            placeholder="Inserisci il termine di ricerca" aria-describedby="searchHelp">
                        <small id="searchHelp" class="form-text text-muted">Inserisci un termine di ricerca per trovare
                            contenuti specifici.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Campo "TAG" -->
                    <div class="form-group mt-4">
                        <label for="tags">Stati:</label>
                        <select class="form-control" id="tags" name="tags[]" multiple>
                            <option value="all" selected>Tutti</option> <!-- Opzione "Tutti" -->
                            <?php foreach ($subTags as $tag): ?>
                                <option value="<?= $tag->id; ?>"><?= htmlspecialchars($tag->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Seleziona gli stati desiderati. Tieni premuto Ctrl (Windows) o
                            Cmd (Mac) per selezionare più di uno.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Campo "CATEGORIA" -->
                    <div class="form-group mt-4">
                        <label for="cat">Campionati:</label>
                        <select class="form-control" id="cat" name="cat[]" multiple>
                            <option value="all" selected>Tutti</option> <!-- Opzione "Tutti" -->
                            <?php foreach ($campionati as $camp): ?>
                                <option value="<?= $camp->id; ?>">
                                    <?= htmlspecialchars($camp->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Seleziona i campionati desiderati. Tieni premuto Ctrl (Windows)
                            o Cmd (Mac) per selezionare più di uno.</small>
                    </div>
                </div>
            </div>
            <!-- Lista articoli -->
            <h4 class="mt-4">Seleziona Squadre: <span id="selected-count">0</span> selezionate</h4>
            <button id="clear-selection" class="btn btn-secondary btn-sm mb-3">Deseleziona tutte</button>
            <div class="row" id="articles-list">
                <?php foreach ($articles as $article): ?>
                    <?php
                    // Recupera il tag della categoria dell'articolo
                    $categoryTag = Competizione::getCategoryTag($article->catid);
                    ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3 catandtag" data-tag="<?= htmlspecialchars($categoryTag); ?>"
                        data-cat="<?= htmlspecialchars($article->catid); ?>"> <!-- Layout responsive con colonne adattive -->
                        <div class="form-check" style="height:52px;">
                            <!-- Input checkbox per selezionare l'articolo -->
                            <input class="form-check-input" type="checkbox" value="<?= $article->id; ?>"
                                id="article-<?= $article->id; ?>" name="articles[]" style="margin-top: 20px;">
                            <label class="form-check-label d-flex align-items-center h-100" for="article-<?= $article->id; ?>">
                                <?php
                                // Decodifica JSON per estrarre l'immagine introduttiva
                                $images = json_decode($article->images);
                                if (isset($images->image_intro)): ?>
                                    <img src="<?= htmlspecialchars($images->image_intro); ?>"
                                        alt="<?= htmlspecialchars($article->title); ?>" class="me-2 miniimg" />
                                    <!-- Aggiunge margine a destra dell'immagine -->
                                <?php else: ?>
                                    <img src="/images/default.webp"
                                        alt="default" class="me-2 miniimg" />
                                <?php endif; ?>
                                <!-- Nome dell'articolo -->
                                <span class="overflow-hidden"><?= htmlspecialchars($article->title); ?></span>
                            </label>
                        </div>
                        <?php
                        $categoryname = Competizione::getCategoryNameById($article->catid);
                        $tagname = Competizione::getTagTitleById($categoryTag);
                        ?>
                        <span class="categoryandtag"><?php echo $tagname . ", " . $categoryname; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div> <!-- Fine container -->
    </form>
<?php endif; ?>


<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-button'])) {
    $gironi = [2, 4, 8];
    $partecipanti = [4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 36, 40, 44, 48, 52, 56, 60, 64, 72, 80, 88, 96, 104, 112, 120, 128];
    $fase_finale = [4, 8, 16, 32, 64, 128];
    // Verifica del parametro "andata_ritorno"
    if ($_POST['andata_ritorno'] != 0 && $_POST['andata_ritorno'] != 1) {
        return;
    }

    // Controlli specifici per le categorie
    if ($this->category->id == 68) {
        if ($_POST['numero_partecipanti'] < 4 || $_POST['numero_partecipanti'] > 24) {
            return;
        }
    } elseif ($this->category->id == 69) {
        if (!in_array($_POST['numero_partecipanti'], $fase_finale)) {
            return;
        }
    } elseif ($this->category->id == 70) {
        if (!in_array($_POST['gironi'], $gironi)) {
            return;
        }
        if (!in_array($_POST['numero_partecipanti'], $partecipanti)) {
            return;
        }
        if (!in_array($_POST['fase_finale'], $fase_finale)) {
            return;
        }
    }
    // Assicurati di convalidare e filtrare i dati di input
    $data = [
        'user_id' => (int) $userId, // ID dell'utente
        'nome_competizione' => $_POST['nome_campionato'], // Nome della competizione
        'modalita' => (int) $this->category->id, // Modalità
        'tipo' => 71,
        'gironi' => isset($_POST['gironi']) ? (int) $_POST['gironi'] : 0, // Gironi
        'andata_ritorno' => (int) $_POST['andata_ritorno'], // Andata/Ritorno
        'partecipanti' => (int) $_POST['numero_partecipanti'], // Partecipanti
        'fase_finale' => isset($_POST['gironi']) ? (int) $_POST['fase_finale'] : 0, // Fase Finale
        'finita' => 0, // Finita, di default a 0
        'squadre' => isset($_POST['articles']) ? $_POST['articles'] : [] // Squadre
    ];

    // Inserisci la competizione
    Competizione::insertCompetizione($data);
    // Ottieni l'URL della voce di menu con ID 106
    $menuLink = Route::_('index.php?Itemid=106');
    header("location: " . $menuLink);
    exit;
}
?>