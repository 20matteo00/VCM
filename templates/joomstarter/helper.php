<?php

namespace Joomstarter\Helpers;

defined(constant_name: '_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Exception;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Symfony\Component\VarDumper\VarDumper;

abstract class Competizione
{
    public static function getCategoriaTag($articleId)
    {
        // Carica l'oggetto articolo
        $article = Table::getInstance('content');
        $article->load($articleId);

        // Recupera l'ID della categoria dell'articolo
        $categoryId = $article->catid;

        // Verifica se esiste una categoria
        if (!$categoryId) {
            return null;
        }

        // Usa il database di Joomla per ottenere i tag associati alla categoria
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('t.id, t.title')
            ->from($db->quoteName('#__tags', 't'))
            ->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm') . ' ON m.tag_id = t.id')
            ->where('m.content_item_id = ' . (int) $categoryId)
            ->where('m.type_alias = ' . $db->quote('com_content.category'))
            ->setLimit(1); // Prende solo un tag

        $db->setQuery($query);
        $tag = $db->loadObject();

        // Se non ci sono tag associati
        if (!$tag) {
            return null;
        }

        // Crea il link alla pagina del tag
        $tagLink = Route::_('index.php?option=com_tags&view=tag&id=' . $tag->id);

        // Restituisce il nome del tag e il link
        return [
            'title' => $tag->title,
            'link' => $tagLink
        ];
    }

    public static function getUrlMenu($menuId)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Creazione della query per ottenere l'elemento di menu con l'ID specificato
        $query = $db->getQuery(true)
            ->select($db->quoteName('link')) // Seleziona il campo 'link' (URL) dell'elemento di menu
            ->from($db->quoteName('#__menu')) // Tabella del menu
            ->where($db->quoteName('id') . ' = ' . (int) $menuId); // Filtro per l'ID della voce di menu
        // Esegui la query
        $db->setQuery($query);

        // Ottieni l'URL dell'elemento di menu
        return $db->loadResult();
    }

    public static function getCategoryTitleById($categoryId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();
        // Creazione della query per ottenere il titolo della categoria
        $query = $db->getQuery(true)
            ->select($db->quoteName('title')) // Seleziona solo il titolo
            ->from($db->quoteName('#__categories')) // Seleziona dalla tabella delle categorie
            ->where($db->quoteName('id') . ' = ' . (int) $categoryId); // Confronta con l'ID della categoria

        // Esegui la query
        $db->setQuery($query);
        return $db->loadResult(); // Carica solo il valore del titolo
    }

    public static function getCustomFields($itemId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Eseguiamo la query per ottenere i campi personalizzati
        $query->select($db->quoteName(['field_id', 'value']))
            ->from($db->quoteName('#__fields_values'))
            ->where($db->quoteName('item_id') . ' = ' . (int) $itemId); // Convertiamo in intero per sicurezza

        $db->setQuery($query);

        // Restituisci i campi personalizzati come array indicizzati per field_id
        return $db->loadObjectList('field_id');
    }

    public static function setCustomFields($idsquadra, $color1, $color2, $forza)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();

        // Iniziamo verificando se i campi esistono già per l'item_id
        $query = $db->getQuery(true)
            ->select('field_id')
            ->from('#__fields_values')
            ->where('item_id = ' . (int) $idsquadra)
            ->where('field_id IN (1, 2, 3)');

        $db->setQuery($query);
        $existingFields = $db->loadColumn(); // Ottiene un array di field_id esistenti

        // Crea la query per l'UPDATE usando CASE, come già fatto
        $updateQuery = $db->getQuery(true)
            ->update('#__fields_values')
            ->set($db->quoteName('value') . ' = CASE ' . $db->quoteName('field_id') .
                ' WHEN 1 THEN ' . $db->quote($color1) .
                ' WHEN 2 THEN ' . $db->quote($color2) .
                ' WHEN 3 THEN ' . $db->quote($forza) .
                ' END')
            ->where('item_id = ' . (int) $idsquadra)
            ->where('field_id IN (1, 2, 3)');

        // Esegui l'UPDATE se ci sono campi da aggiornare
        if (!empty($existingFields)) {
            $db->setQuery($updateQuery);
            $db->execute();
        }

        // Aggiungi i nuovi campi se non esistono
        if (empty($existingFields) || count($existingFields) < 3) {
            $insertQuery = $db->getQuery(true)
                ->insert('#__fields_values')
                ->columns(['item_id', 'field_id', 'value']);

            if (!in_array(1, $existingFields)) {
                $insertQuery->values((int) $idsquadra . ', 1, ' . $db->quote($color1));
            }
            if (!in_array(2, $existingFields)) {
                $insertQuery->values((int) $idsquadra . ', 2, ' . $db->quote($color2));
            }
            if (!in_array(3, $existingFields)) {
                $insertQuery->values((int) $idsquadra . ', 3, ' . $db->quote($forza));
            }

            // Esegui l'inserimento dei nuovi record
            if (isset($insertQuery)) {
                $db->setQuery($insertQuery);
                $db->execute();
            }
        }
    }


    public static function getArticlesFromSubcategories($categoryId, $userId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Query per ottenere gli articoli delle sottocategorie della categoria specificata
        $query->select('a.id, a.title, a.images, a.catid, a.created, c.title as category_title, f1.value as color1, f2.value as color2, f3.value as number_value')
            ->from('#__content AS a')
            ->join('INNER', '#__categories AS c ON a.catid = c.id')
            ->join('LEFT', '#__fields_values AS f1 ON f1.item_id = a.id AND f1.field_id = 1') // Colore 1
            ->join('LEFT', '#__fields_values AS f2 ON f2.item_id = a.id AND f2.field_id = 2') // Colore 2
            ->join('LEFT', '#__fields_values AS f3 ON f3.item_id = a.id AND f3.field_id = 3') // Numero
            ->where('c.parent_id = ' . (int) $categoryId)
            ->where('a.created_by IN (' . (int) $userId . ', 988)')
            ->order('c.id ASC, CAST(f3.value AS UNSIGNED) DESC, a.title ASC'); // Ordina prima per ID categoria e poi per titolo dell'articolo

        $db->setQuery($query);

        // Restituisci gli articoli come array di oggetti
        return $db->loadObjectList();
    }

    public static function getArticlesFromSubcategoriesPagination($categoryId, $userId, $limit, $limitstart)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();

        $query = $db->getQuery(true);

        // Query per ottenere gli articoli delle sottocategorie della categoria specificata
        $query->select('a.id, a.title, a.images, a.catid, a.created, c.title as category_title, c.params as category_params,f1.value as color1, f2.value as color2, f3.value as number_value')
            ->from('#__content AS a')
            ->join('INNER', '#__categories AS c ON a.catid = c.id')
            ->join('LEFT', '#__fields_values AS f1 ON f1.item_id = a.id AND f1.field_id = 1') // Colore 1
            ->join('LEFT', '#__fields_values AS f2 ON f2.item_id = a.id AND f2.field_id = 2') // Colore 2
            ->join('LEFT', '#__fields_values AS f3 ON f3.item_id = a.id AND f3.field_id = 3') // Numero
            ->where('c.parent_id = ' . (int) $categoryId)
            ->where('a.created_by IN (' . (int) $userId . ', 988)')
            ->order('c.id ASC, CAST(f3.value AS UNSIGNED) DESC, a.title ASC') // Ordina prima per ID categoria e poi per titolo dell'articolo
            ->setLimit($limit, $limitstart); // Imposta il limite e l'inizio

        $db->setQuery($query);

        // Restituisci gli articoli come array di oggetti
        return $db->loadObjectList();
    }


    public static function getTotalArticlesFromSubcategories($categoryId, $userId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('COUNT(a.id)')
            ->from('#__content AS a')
            ->join('INNER', '#__categories AS c ON a.catid = c.id')
            ->where('c.parent_id = ' . (int) $categoryId)
            ->where('a.created_by IN (' . (int) $userId . ', 988)')
        ;

        $db->setQuery($query);

        // Restituisci il numero totale di articoli
        return $db->loadResult();
    }


    public static function getCategoryUrlByArticleId($articleId)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere l'ID della categoria
        $query = $db->getQuery(true)
            ->select($db->quoteName('catid'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('id') . ' = ' . (int) $articleId);

        // Esegui la query
        $db->setQuery($query);
        $categoryId = $db->loadResult();

        // Verifica se l'ID della categoria è stato trovato
        if ($categoryId) {
            // Ottieni l'URL della categoria
            $categoryUrl = Route::_(RouteHelper::getCategoryRoute($categoryId));
            return $categoryUrl;
        }

        return null; // Restituisce null se la categoria non è trovata
    }

    public static function getArticlesFromCategory($categoryId, $userId)
    {
        // Ottieni il database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Query per ottenere gli articoli della categoria e i valori degli extra fields
        $query->select('a.id, a.title, a.images, a.catid, a.created, c.title as category_title, f1.value as color1, f2.value as color2, CAST(f3.value AS UNSIGNED) as forza')
            ->from('#__content as a')
            ->join('LEFT', '#__categories as c ON a.catid = c.id') // Aggiungi la join per il titolo della categoria
            ->join('LEFT', '#__fields_values AS f1 ON f1.item_id = a.id AND f1.field_id = 1') // Extra field Colore 1
            ->join('LEFT', '#__fields_values AS f2 ON f2.item_id = a.id AND f2.field_id = 2') // Extra field Colore 2
            ->join('LEFT', '#__fields_values AS f3 ON f3.item_id = a.id AND f3.field_id = 3') // Extra field Forza
            ->where('a.catid = ' . (int) $categoryId) // Filtro per la categoria corrente
            ->where('a.created_by IN (' . (int) $userId . ', 988)')
            ->where('a.state = 1') // Solo articoli pubblicati
            ->order('CAST(f3.value AS UNSIGNED) DESC, a.title ASC'); // Ordina per forza in modo numerico, poi per titolo

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    // Funzione per ottenere il titolo dell'articolo
    public static function getArticleTitleById($articleId)
    {
        $db = Factory::getDbo();
        return $db->setQuery("SELECT title FROM #__content WHERE id = " . (int) $articleId)->loadResult() ?: '';
    }

    // Funzione per ottenere l'URL dell'articolo
    public static function getArticleUrlById($articleId)
    {
        $db = Factory::getDbo();
        $article = $db->setQuery("SELECT id, alias, catid FROM #__content WHERE id = " . (int) $articleId)->loadObject();

        return $article ? Route::_('index.php?option=com_content&view=article&id=' . (int) $articleId . '&catid=' . (int) $article->catid) : '';
    }
    // Funzione per recuperare gli articoli in base alle sottocategorie
    public static function getArticlesInSubcategories($subcategoryIds, $userId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title, images, catid, hits') // Aggiungi 'hits' qui per ordinare
            ->from('#__content')
            ->where('catid IN (' . implode(',', array_map('intval', $subcategoryIds)) . ')')
            ->where('state = 1') // Solo articoli pubblicati
            ->where('created_by IN (' . (int) $userId . ', 988)')
            ->order('catid ASC, hits DESC'); // Ordina prima per 'catid' in ordine crescente, poi per 'hits' in ordine decrescente

        return $db->setQuery($query)->loadObjectList();
    }

    // Funzione per recuperare il titolo della categoria
    public static function getCategoryNameById($categoryId)
    {
        $db = Factory::getDbo();
        return $db->setQuery("SELECT title FROM #__categories WHERE id = " . (int) $categoryId)->loadResult() ?: '';
    }
    // Funzione per recuperare le sottocategorie di una data categoria per ricavare gli articoli
    public static function getSubcategories($categoryId, $asObject = false)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($asObject ? 'id, title' : 'id')
            ->from('#__categories')
            ->where('parent_id = ' . (int) $categoryId);

        return $asObject ? $db->setQuery($query)->loadObjectList() : $db->setQuery($query)->loadColumn();
    }
    // Funzione per recuperare i sottotag di un tag specifico
    public static function getSubTags($tagId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__tags')
            ->where('parent_id = ' . (int) $tagId);

        return $db->setQuery($query)->loadObjectList();
    }
    // Funzione per recuperare il tag associato alla categoria dell'articolo
    public static function getCategoryTag($categoryId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('t.id')
            ->from('#__tags AS t')
            ->join('INNER', '#__contentitem_tag_map AS m ON m.tag_id = t.id')
            ->where('m.type_alias = "com_content.category"')
            ->where('m.content_item_id = ' . (int) $categoryId)
            ->where('t.published = 1'); // Solo tag pubblicati

        return $db->setQuery($query)->loadResult();
    }

    public static function getTagTitleById($tagId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();

        // Creazione della query per ottenere il titolo del tag
        $query = $db->getQuery(true)
            ->select($db->quoteName('title')) // Seleziona il titolo del tag
            ->from($db->quoteName('#__tags')) // Seleziona dalla tabella dei tag
            ->where($db->quoteName('id') . ' = ' . (int) $tagId); // Filtra per ID del tag

        // Esegui la query
        $db->setQuery($query);
        return $db->loadResult(); // Carica solo il valore del titolo
    }


    // Funzione per recuperare una competizione dal database in base all'ID della competizione e all'ID dell'utente
    public static function getCompetizioneById($idcomp, $userId)
    {
        // Connessione al database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Costruisci la query per selezionare i dati della competizione basata sull'ID della competizione e dell'utente
        $query->select('*')
            ->from($db->quoteName('#__competizioni')) // Sostituisci con il nome corretto della tua tabella
            ->where($db->quoteName('id') . ' = ' . $db->quote($idcomp))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
            ->order($db->quoteName('creazione') . ' DESC'); // Aggiungi il controllo dell'ID utente

        // Esegui la query
        $db->setQuery($query);

        // Recupera la competizione
        return $db->loadObject();
    }

    public static function getCompetizioniPerUtente($userId, $finita, $limit = 10, $offset = 0)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__competizioni'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
            ->where($db->quoteName('finita') . ' = ' . $finita)
            ->setLimit($limit, $offset)
            ->order($db->quoteName('creazione') . ' DESC'); // Aggiungi il limite e l'offset

        $db->setQuery($query);
        return $db->loadObjectList();
    }


    public static function countCompetizioniPerUtente($userId, $finita)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__competizioni'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
            ->where($db->quoteName('finita') . ' = ' . $finita);

        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    public static function getCompetizioniPerTipo($userId, $tipo, $limit = 0, $offset = 0)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__competizioni'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
            ->where($db->quoteName('finita') . ' = 1')
            ->where($db->quoteName('tipo') . ' = ' . $tipo)
            ->setLimit($limit, $offset)
            ->order($db->quoteName('creazione') . ' DESC'); // Aggiungi il limite e l'offset

        $db->setQuery($query);
        return $db->loadObjectList();
    }


    public static function countCompetizioniPerTipo($userId, $tipo)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__competizioni'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
            ->where($db->quoteName('finita') . ' = 1')
            ->where($db->quoteName('tipo') . ' = ' . $tipo)
        ;

        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    // Funzione per inserire una competizione nella tabella
    public static function insertCompetizione($data)
    {
        $db = Factory::getDbo();
        $tableName = $db->getPrefix() . 'competizioni';

        // Trova il primo ID mancante (il più basso disponibile)
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName($tableName))
            ->order('id ASC');  // Ordina per ID in ordine crescente
        $db->setQuery($query);

        // Ottieni tutti gli ID presenti nella tabella
        $ids = $db->loadColumn();

        // Trova il primo ID disponibile (manualmente)
        $nextId = 1; // Partiamo dal primo ID
        foreach ($ids as $id) {
            if ($id != $nextId) {
                break;  // Trova il primo ID mancante
            }
            $nextId++;  // Se l'ID corrente è presente, prova con il successivo
        }

        // Ora prepariamo l'inserimento con l'ID trovato
        $query = $db->getQuery(true);
        $columns = ['id', 'user_id', 'nome_competizione', 'modalita', 'tipo', 'gironi', 'andata_ritorno', 'partecipanti', 'fase_finale', 'finita', 'squadre'];
        $values = [
            (int) $nextId, // Usa il primo ID disponibile
            (int) $data['user_id'],
            $db->quote($data['nome_competizione']),
            (int) $data['modalita'],
            (int) $data['tipo'],
            (int) $data['gironi'],
            (int) $data['andata_ritorno'],
            (int) $data['partecipanti'],
            (int) $data['fase_finale'],
            (int) $data['finita'],
            $db->quote(json_encode($data['squadre'])) // Codifica l'array in JSON
        ];

        // Creiamo la query di inserimento
        $query
            ->insert($db->quoteName($tableName))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        // Eseguiamo la query
        $db->setQuery($query);
        $db->execute();
    }

    public static function CreaTabelleCompetizione($idCompetizione, $squadre)
    {
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $tablePartite = $prefix . 'competizione' . $idCompetizione . '_partite';
        $tableStatistiche = $prefix . 'competizione' . $idCompetizione . '_statistiche';

        // Creazione della tabella partite
        $query = "CREATE TABLE IF NOT EXISTS `$tablePartite` (
        `squadra1` INT NOT NULL,
        `squadra2` INT NOT NULL,
        `gol1` INT DEFAULT NULL,
        `gol2` INT DEFAULT NULL,
        `giornata` INT NOT NULL,
        `girone` INT DEFAULT NULL,
        PRIMARY KEY (`squadra1`, `squadra2`, `giornata`)
    )";
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            echo 'Errore nella creazione della tabella partite: ' . $e->getMessage();
        }

        // Creazione della tabella statistiche
        $query = "CREATE TABLE IF NOT EXISTS `$tableStatistiche` (
        `squadra` INT NOT NULL,
        `VC` INT DEFAULT NULL,
        `NC` INT DEFAULT NULL,
        `PC` INT DEFAULT NULL,
        `GFC` INT DEFAULT NULL,
        `GSC` INT DEFAULT NULL,
        `VT` INT DEFAULT NULL,
        `NT` INT DEFAULT NULL,
        `PT` INT DEFAULT NULL,
        `GFT` INT DEFAULT NULL,
        `GST` INT DEFAULT NULL,
        `girone` INT DEFAULT NULL,
        PRIMARY KEY (`squadra`)
    )";
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            echo 'Errore nella creazione della tabella statistiche: ' . $e->getMessage();
        }

        // Popola la tabella statistiche con tutte le squadre della competizione
        foreach ($squadre as $squadraId) {
            $query = "INSERT IGNORE INTO `$tableStatistiche` (`squadra`) VALUES (" . (int) $squadraId . ")";
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                echo 'Errore durante l\'inserimento nella tabella statistiche: ' . $e->getMessage();
            }
        }
    }

    public static function GeneraCampionato($squadre, $tablePartite, $ar, $champ, $gir)
    {
        $db = Factory::getDbo();

        // Verifica se la tabella è vuota
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tablePartite));

        $db->setQuery($query);
        $count = $db->loadResult();

        if ($count == 0 || $champ) {
            // Procedi solo se ci sono già dati nella tabella
            $giornate = [];
            $numeroSquadre = count($squadre);

            if ($numeroSquadre == 0) {
                return; // O gestisci l'errore come preferisci
            }

            shuffle($squadre);

            if ($numeroSquadre % 2 != 0) {
                $squadre[] = "0";
                $numeroSquadre++;
            }

            for ($giornata = 0; $giornata < $numeroSquadre - 1; $giornata++) {
                $partite = [];
                for ($i = 0; $i < $numeroSquadre / 2; $i++) {
                    $squadraCasa = $squadre[$i];
                    $squadraTrasferta = $squadre[$numeroSquadre - 1 - $i];

                    $partite[] = [
                        'squadra1' => $squadraCasa,
                        'squadra2' => $squadraTrasferta,
                    ];
                }
                if (!empty($partite)) {
                    $giornate[] = $partite;
                }
                $squadre = array_merge(
                    [$squadre[0]],
                    array_slice($squadre, 2),
                    [$squadre[1]]
                );
            }
            if ($champ)
                $girone = $gir + 1;
            else
                $girone = null;
            $numeroSquadre = count($squadre);
            foreach ($giornate as $index => $partite) {
                foreach ($partite as $partita) {
                    // Inserisci la partita di andata nel DB
                    if ($partita['squadra1'] == "0" || $partita['squadra2'] == "0")
                        continue;
                    $inserimento = (object) [
                        'squadra1' => $partita['squadra1'],
                        'squadra2' => $partita['squadra2'],
                        'giornata' => $index + 1,
                        'girone' => $girone,
                    ];

                    // Esegui l'inserimento
                    try {
                        $db->insertObject($tablePartite, $inserimento);
                    } catch (Exception $e) {
                        echo 'Error inserting first match: ' . $e->getMessage();
                        // Puoi anche loggare l'errore o fare altre operazioni
                    }
                    if ($ar == 1) {
                        // Inserisci la partita di ritorno
                        $inserimentoRitorno = (object) [
                            'squadra1' => $partita['squadra2'],
                            'squadra2' => $partita['squadra1'],
                            'giornata' => $numeroSquadre + $index,
                            'girone' => $girone,
                        ];

                        // Esegui l'inserimento
                        try {
                            $db->insertObject($tablePartite, $inserimentoRitorno);
                        } catch (Exception $e) {
                            echo 'Error inserting return match: ' . $e->getMessage();
                            // Puoi anche loggare l'errore o fare altre operazioni
                        }
                    }
                }
            }
        }
    }

    public static function GeneraEliminazione($squadre, $tablePartite, $ar)
    {
        $db = Factory::getDbo();

        // Controlla se ci sono già partite nella tabella
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite));
        $db->setQuery($query);
        $partiteEsistenti = $db->loadObjectList();

        // Se non ci sono partite nella tabella, crea il primo turno con squadre mischiate
        if (empty($partiteEsistenti)) {
            //shuffle($squadre); // Mischia le squadre
            self::creaTurno($squadre, 1, $tablePartite, $ar);
            return;
        }

        // Controlla se ci sono partite incomplete nel turno corrente
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('gol1') . ' IS NULL OR ' . $db->quoteName('gol2') . ' IS NULL');
        $db->setQuery($query);
        $partiteIncomplete = $db->loadObjectList();

        // Se ci sono partite incomplete, interrompi l'esecuzione
        if (!empty($partiteIncomplete)) {
            return;
        }

        // Controlla se ci sono già partite per il turno corrente
        $turnoCorrente = self::getTurnoCorrente($tablePartite);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' = ' . (int) $turnoCorrente);
        $db->setQuery($query);
        $partiteTurnoCorrente = $db->loadObjectList();

        // Se ci sono già partite per il turno corrente, non generare un nuovo turno
        if (!empty($partiteTurnoCorrente)) {
            return;
        }

        // Recupera le squadre vincenti dal turno precedente
        $squadreVincenti = [];
        $finale = self::isFinale($tablePartite);
        if ($ar === 0 || $finale)
            $turnoPrecedente = self::getTurnoCorrente($tablePartite);
        elseif ($ar === 1)
            $turnoPrecedente = self::getTurnoCorrente($tablePartite) - 1;

        $query = $db->getQuery(true)
            ->select('squadra1, squadra2, gol1, gol2, giornata')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' = ' . (int) $turnoPrecedente . ' OR ' . $db->quoteName('giornata') . ' = ' . (int) ($turnoPrecedente - 1));
        $db->setQuery($query);
        $partiteTurnoPrecedente = $db->loadObjectList();

        // Raccogli i risultati sommando i gol per ogni coppia di squadre (andata e ritorno)
        $risultati = [];
        foreach ($partiteTurnoPrecedente as $partita) {
            // Ignora partite con gol NULL
            if ($partita->gol1 === null || $partita->gol2 === null) {
                continue;
            }

            // Creazione della chiave unica per le squadre
            $key = min($partita->squadra1, $partita->squadra2) . '-' . max($partita->squadra2, $partita->squadra1);


            // Somma i gol di andata e ritorno
            if ($ar === 0) {
                // Se non esiste già un record per questa coppia, crealo
                if (!isset($risultati[$key])) {
                    $risultati[$key] = [
                        'squadra1' => $partita->squadra1,
                        'squadra2' => $partita->squadra2,
                        'gol1' => 0,
                        'gol2' => 0,
                        'partite' => [] // Aggiungi un array per tenere traccia delle partite
                    ];
                }
            } else {
                // In caso di eliminazione, gestisci andata e ritorno
                if ($partita->giornata % 2 == 1) { // Considera come andata
                    // Se non esiste già un record per questa coppia, crealo
                    if (!isset($risultati[$key])) {
                        $risultati[$key] = [
                            'squadra1' => $partita->squadra1,
                            'squadra2' => $partita->squadra2,
                            'gol1' => 0,
                            'gol2' => 0,
                            'partite' => [] // Aggiungi un array per tenere traccia delle partite
                        ];
                    }
                } elseif ($partita->giornata % 2 == 0) { // Considera come ritorno
                    // Somma i gol invertiti per la partita di ritorno
                    // Se non esiste già un record per questa coppia, crealo
                    if (!isset($risultati[$key])) {
                        $risultati[$key] = [
                            'squadra1' => $partita->squadra2,
                            'squadra2' => $partita->squadra1,
                            'gol1' => 0,
                            'gol2' => 0,
                            'partite' => [] // Aggiungi un array per tenere traccia delle partite
                        ];
                    }
                }
            }
            // Aggiungi la partita all'array delle partite
            $risultati[$key]['partite'][] = $partita;

            // Somma i gol di andata e ritorno
            if ($ar === 0) {
                // In caso di girone, somma normalmente
                $risultati[$key]['gol1'] += $partita->gol1;
                $risultati[$key]['gol2'] += $partita->gol2;
            } else {
                // In caso di eliminazione, gestisci andata e ritorno
                if ($partita->giornata % 2 == 1) { // Considera come andata
                    $risultati[$key]['gol1'] += $partita->gol1; // gol squadra1
                    $risultati[$key]['gol2'] += $partita->gol2; // gol squadra2
                } elseif ($partita->giornata % 2 == 0) { // Considera come ritorno
                    // Somma i gol invertiti per la partita di ritorno
                    $risultati[$key]['gol1'] += $partita->gol2; // gol della squadra1 nel ritorno
                    $risultati[$key]['gol2'] += $partita->gol1; // gol della squadra2 nel ritorno
                }
            }
        }

        // Stampa i risultati per debug
        /* foreach ($risultati as $key => $risultato) {
            echo "Risultati: " . self::getArticleTitleById($risultato['squadra1']) . " vs " . self::getArticleTitleById($risultato['squadra2']) . " - Gol: {$risultato['gol1']} : {$risultato['gol2']}\n";
        } */

        // Determina le squadre vincenti
        foreach ($risultati as $key => $risultato) {
            if ($risultato['gol1'] > $risultato['gol2']) {
                $squadreVincenti[] = $risultato['squadra1'];
            } elseif ($risultato['gol2'] > $risultato['gol1']) {
                $squadreVincenti[] = $risultato['squadra2'];
            } else {

                // Messaggio di alert
                $message = "Pareggio tra " . self::getArticleTitleById(articleId: $risultato['squadra1']) . " e " . self::getArticleTitleById(articleId: $risultato['squadra2']);

                // Codice JavaScript con SweetAlert2
                echo "
                    <script>
                        Swal.fire({
                            title: 'Attenzione!',
                            text: '" . addslashes($message) . "',
                            icon: 'warning',
                            confirmButtonText: 'Ok'
                        });
                    </script>";


                return;
            }
        }
        $squadreVincenti = array_unique($squadreVincenti);
        // Se non ci sono abbastanza squadre per un nuovo turno, il torneo è terminato
        if (count($squadreVincenti) < 2) {
            //echo "Il torneo è terminato. Vincitore: " . reset($squadreVincenti);
            return;
        }

        // Creazione delle partite per il nuovo turno
        if ($ar === 0)
            $turno = $turnoPrecedente;
        else
            $turno = $turnoPrecedente + 1;
        self::creaTurno($squadreVincenti, $turno, $tablePartite, $ar);
    }

    public static function creaTurno($squadre, $turno, $tablePartite, $ar)
    {
        $squadre = array_values($squadre); // Riassegna indici consecutivi a partire da 0
        $db = Factory::getDbo();
        $partite = [];
        $numsquadre = count($squadre);

        for ($i = 0; $i < floor($numsquadre / 2); $i++) {
            // Controlla che gli indici esistano nell'array prima di accedervi
            $squadraCasa = isset($squadre[$i]) ? $squadre[$i] : null;
            $squadraTrasferta = isset($squadre[$numsquadre - 1 - $i]) ? $squadre[$numsquadre - 1 - $i] : null;

            // Verifica che entrambe le squadre siano definite e non vuote
            if (!empty($squadraCasa) && !empty($squadraTrasferta)) {
                // Aggiunge la partita di andata
                $partite[] = (object) [
                    'squadra1' => $squadraCasa,
                    'squadra2' => $squadraTrasferta,
                    'giornata' => $turno,
                    'gol1' => null,
                    'gol2' => null
                ];

                // Aggiunge la partita di ritorno se `$ar` è 1
                if ($ar === 1 && $numsquadre > 2) {
                    $partite[] = (object) [
                        'squadra1' => $squadraTrasferta,
                        'squadra2' => $squadraCasa,
                        'giornata' => $turno + 1,
                        'gol1' => null,
                        'gol2' => null
                    ];
                }
            }
        }

        // Inserisci le partite nel database
        foreach ($partite as $partita) {
            $query = $db->getQuery(true)
                ->insert($db->quoteName($tablePartite))
                ->columns([
                    $db->quoteName('squadra1'),
                    $db->quoteName('squadra2'),
                    $db->quoteName('giornata'),
                    $db->quoteName('gol1'),
                    $db->quoteName('gol2')
                ])
                ->values(
                    $db->quote($partita->squadra1) . ', ' .
                    $db->quote($partita->squadra2) . ', ' .
                    $db->quote($partita->giornata) . ', ' .
                    'NULL, NULL'
                );

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                echo 'Errore nell\'inserimento della partita: ' . $e->getMessage();
            }
        }
    }

    // Funzione di supporto per ottenere il turno corrente
    public static function getTurnoCorrente($tablePartite)
    {
        $db = Factory::getDbo();

        // Ottiene il turno più alto presente nel database
        $query = $db->getQuery(true)
            ->select('MAX(' . $db->quoteName('giornata') . ')')
            ->from($db->quoteName($tablePartite));
        $db->setQuery($query);
        $maxTurno = $db->loadResult();

        return $maxTurno ? $maxTurno + 1 : 1;
    }

    public static function GeneraChampions($squadre, $tablePartite, $ar, $gironi)
    {
        if (count($squadre) % $gironi !== 0)
            return;
        $db = Factory::getDbo();

        // Verifica se la tabella è vuota
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tablePartite));

        $db->setQuery($query);
        $count = $db->loadResult();

        if ($count == 0) {
            // Mescola casualmente l'array
            shuffle($squadre);


            // Calcola il numero di elementi per ogni sotto-array
            $numsquadre = count($squadre);
            $elementiPerSottoArray = floor($numsquadre / $gironi);

            // Crea i sotto-array
            $final = [];
            for ($i = 0; $i < $gironi; $i++) {
                // Dividi l'array usando array_slice
                $final[] = array_slice($squadre, $i * $elementiPerSottoArray, $elementiPerSottoArray);
                self::GeneraCampionato($final[$i], $tablePartite, $ar, true, $i);
            }
        }

    }

    public static function GeneraStatistiche($squadre, $tableStatistiche, $tablePartite, $mod)
    {
        $db = Factory::getDbo();


        // Inizializza le statistiche
        $statistiche = [];

        // Inizializza le statistiche per ogni squadra a null
        foreach ($squadre as $squadraId) {
            $statistiche[$squadraId] = [
                'VC' => null, // Vittorie in casa
                'NC' => null, // Nulle in casa
                'PC' => null, // Perde in casa
                'GFC' => null, // Gol Fatti
                'GSC' => null, // Gol Subiti
                'VT' => null,
                'NT' => null,
                'PT' => null,
                'GFT' => null,
                'GST' => null,
            ];
        }

        // Prepara la query per ottenere tutte le partite
        $query = $db->getQuery(true)
            ->select('squadra1, squadra2, gol1, gol2')
            ->from($db->quoteName($tablePartite));

        $db->setQuery($query);

        try {
            $partite = $db->loadObjectList();
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle partite: ' . $e->getMessage();
            return;
        }

        // Calcola le statistiche per ogni partita
        foreach ($partite as $partita) {
            $squadra1 = $partita->squadra1;
            $squadra2 = $partita->squadra2;
            $gol1 = $partita->gol1;
            $gol2 = $partita->gol2;

            // Salta le partite non giocate (gol null)
            if ($gol1 === null || $gol2 === null) {
                continue;
            }

            // Aggiorna le statistiche in base al risultato
            if ($gol1 > $gol2) { // Squadra 1 vince
                $statistiche[$squadra1]['VC']++;
                $statistiche[$squadra2]['PT']++;
            } elseif ($gol1 < $gol2) { // Squadra 2 vince
                $statistiche[$squadra2]['VT']++;
                $statistiche[$squadra1]['PC']++;
            } else { // Pareggio
                $statistiche[$squadra1]['NC']++;
                $statistiche[$squadra2]['NT']++;
            }

            // Aggiorna gol fatti e subiti
            $statistiche[$squadra1]['GFC'] += $gol1;
            $statistiche[$squadra1]['GSC'] += $gol2;
            $statistiche[$squadra2]['GFT'] += $gol2;
            $statistiche[$squadra2]['GST'] += $gol1;
        }

        // Aggiorna la tabella statistiche nel database
        foreach ($squadre as $squadraId) {
            if ($mod === 70) {
                $girone = self::getGironeBySquadraId($squadraId, $tablePartite);
            } else {
                $girone = null;
            }
            $query = $db->getQuery(true)
                ->update($db->quoteName($tableStatistiche))
                ->set($db->quoteName('VC') . ' = ' . ($statistiche[$squadraId]['VC'] ?? 'NULL'))
                ->set($db->quoteName('NC') . ' = ' . ($statistiche[$squadraId]['NC'] ?? 'NULL'))
                ->set($db->quoteName('PC') . ' = ' . ($statistiche[$squadraId]['PC'] ?? 'NULL'))
                ->set($db->quoteName('GFC') . ' = ' . ($statistiche[$squadraId]['GFC'] ?? 'NULL'))
                ->set($db->quoteName('GSC') . ' = ' . ($statistiche[$squadraId]['GSC'] ?? 'NULL'))
                ->set($db->quoteName('VT') . ' = ' . ($statistiche[$squadraId]['VT'] ?? 'NULL'))
                ->set($db->quoteName('NT') . ' = ' . ($statistiche[$squadraId]['NT'] ?? 'NULL'))
                ->set($db->quoteName('PT') . ' = ' . ($statistiche[$squadraId]['PT'] ?? 'NULL'))
                ->set($db->quoteName('GFT') . ' = ' . ($statistiche[$squadraId]['GFT'] ?? 'NULL'))
                ->set($db->quoteName('GST') . ' = ' . ($statistiche[$squadraId]['GST'] ?? 'NULL'))
                ->set($db->quoteName('girone') . ' = ' . ($girone ?? 'NULL'))
                ->where($db->quoteName('squadra') . ' = ' . (int) $squadraId);

            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                echo 'Errore durante l\'aggiornamento delle statistiche: ' . $e->getMessage();
            }
        }
    }

    // Funzione per ottenere la classifica delle squadre
    public static function getClassifica($tableStatistiche)
    {
        $db = Factory::getDbo();

        // Query per ottenere tutte le statistiche e calcolare i punti, la differenza reti e i gol fatti
        $query = $db->getQuery(true)
            ->select('*')
            ->select('
            (COALESCE(VC, 0) + COALESCE(VT, 0)) * 3 + (COALESCE(NC, 0) + COALESCE(NT, 0)) AS punti,  
            (COALESCE(GFC, 0) + COALESCE(GFT, 0) - COALESCE(GSC, 0) - COALESCE(GST, 0)) AS diff_reti, 
            (COALESCE(GFC, 0) + COALESCE(GFT, 0)) AS gol_fatti')
            ->from($db->quoteName($tableStatistiche))
            ->order('punti DESC, diff_reti DESC, gol_fatti DESC, squadra ASC'); // Ordina secondo i criteri specificati

        $db->setQuery($query);

        try {
            return $db->loadObjectList(); // Restituisce un array di oggetti
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle statistiche: ' . $e->getMessage();
            return [];
        }
    }

    public static function getClassificaAR($tablePartite, $ar, $numsquadre, $view, $mod, $gironi)
    {
        if ($ar === 0) {
            return [];
        }

        if ($ar === 1) {
            $classifica = []; // Array per contenere la classifica delle squadre

            // Ottieni il database
            $db = Factory::getDbo();

            // Crea la query per ottenere le partite
            $query = $db->getQuery(true)
                ->select('*') // Seleziona tutti i campi
                ->from($db->quoteName($tablePartite)); // Sostituisci con il nome della tua tabella

            // Esegui la query
            $db->setQuery($query);
            $partite = $db->loadObjectList(); // Ottieni i risultati come array di oggetti

            // Controlla se $partite è valido
            if (!is_array($partite) && !is_object($partite)) {
                return []; // Gestisci l'errore
            }

            // Itera su tutte le partite
            foreach ($partite as $partita) {
                // Controlla se la partita è stata giocata nella giornata valida
                if ($view === "andata") {
                    if ($mod === 68)
                        $partitedaprendere = $partita->giornata < $numsquadre;
                    elseif ($mod === 69)
                        $partitedaprendere = $partita->giornata % 2 == 1;
                    elseif ($mod === 70)
                        $partitedaprendere = $partita->giornata < ($numsquadre / $gironi);
                } elseif ($view === "ritorno") {
                    if ($mod === 68)
                        $partitedaprendere = $partita->giornata >= $numsquadre;
                    elseif ($mod === 69)
                        $partitedaprendere = $partita->giornata % 2 == 0;
                    elseif ($mod === 70)
                        $partitedaprendere = $partita->giornata >= ($numsquadre / $gironi);
                }
                if ($partitedaprendere) {
                    // Estrai le squadre e i risultati
                    $squadraCasa = $partita->squadra1; // ID della squadra di casa
                    $squadraTrasferta = $partita->squadra2; // ID della squadra in trasferta
                    $golCasa = $partita->gol1; // Gol della squadra di casa
                    $golTrasferta = $partita->gol2; // Gol della squadra in trasferta
                    if ($golCasa === NULL || $golTrasferta === NULL)
                        continue;
                    // Inizializza le squadre se non già presente
                    if (!isset($classifica[$squadraCasa])) {
                        $classifica[$squadraCasa] = new \stdClass();
                        $classifica[$squadraCasa]->ID = $squadraCasa;
                        $classifica[$squadraCasa]->V = 0;
                        $classifica[$squadraCasa]->N = 0;
                        $classifica[$squadraCasa]->P = 0;
                        $classifica[$squadraCasa]->GF = 0;
                        $classifica[$squadraCasa]->GS = 0;
                    }
                    if (!isset($classifica[$squadraTrasferta])) {
                        $classifica[$squadraTrasferta] = new \stdClass();
                        $classifica[$squadraTrasferta]->ID = $squadraTrasferta;
                        $classifica[$squadraTrasferta]->V = 0;
                        $classifica[$squadraTrasferta]->N = 0;
                        $classifica[$squadraTrasferta]->P = 0;
                        $classifica[$squadraTrasferta]->GF = 0;
                        $classifica[$squadraTrasferta]->GS = 0;
                    }

                    // Calcola i risultati
                    if ($golCasa > $golTrasferta) {
                        // Vittoria per la squadra di casa
                        $classifica[$squadraCasa]->V++;
                        $classifica[$squadraTrasferta]->P++;
                    } elseif ($golCasa < $golTrasferta) {
                        // Vittoria per la squadra in trasferta
                        $classifica[$squadraTrasferta]->V++;
                        $classifica[$squadraCasa]->P++;
                    } else {
                        // Pareggio
                        $classifica[$squadraCasa]->N++;
                        $classifica[$squadraTrasferta]->N++;
                    }

                    // Aggiorna i gol fatti e subiti
                    $classifica[$squadraCasa]->GF += $golCasa;
                    $classifica[$squadraCasa]->GS += $golTrasferta;
                    $classifica[$squadraTrasferta]->GF += $golTrasferta;
                    $classifica[$squadraTrasferta]->GS += $golCasa;
                }
            }

            return array_values($classifica); // Restituisci un array di oggetti
        }

        return []; // Se non ci sono altre condizioni, restituisci un array vuoto
    }

    public static function getGiornateByCompetizioneId($idcomp, $tablePartite)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite)) // Sostituisci con il nome corretto della tua tabella
            ->order($db->quoteName('giornata') . ' ASC')
            ->order($db->quoteName('girone') . ' ASC')
            /* ->order('LEAST(' . $db->quoteName('squadra1') . ', ' . $db->quoteName('squadra2') . ') ASC') // Ordina per il min id tra squadra1 e squadra2
            ->order('GREATEST(' . $db->quoteName('squadra1') . ', ' . $db->quoteName('squadra2') . ') ASC') */ ; // Ordina per il max id tra squadra1 e squadra2

        $db->setQuery($query);
        return $db->loadObjectList(); // Restituisce un array di oggetti
    }

    public static function getNumeroPartite($tablePartite)
    {
        // Connessione al database
        $db = Factory::getDbo(); // Ottieni l'oggetto database di Joomla

        // Costruzione della query
        $query = $db->getQuery(true); // Crea un nuovo oggetto query
        $query->select('COUNT(*)') // Seleziona il conteggio totale
            ->from($db->quoteName($tablePartite)) // Nome della tabella
            ->where($db->quoteName('gol1') . ' IS NOT NULL') // Controlla che gol1 non sia null
            ->where($db->quoteName('gol2') . ' IS NOT NULL'); // Controlla che gol2 non sia null

        // Esecuzione della query
        $db->setQuery($query);
        $numeroPartite = $db->loadResult(); // Carica il risultato della query

        return $numeroPartite; // Restituisce il numero di partite
    }

    public static function getSquadreOrdinate($squadre)
    {
        $nomisquadre = [];

        // Popola l'array con ID e nome della squadra
        foreach ($squadre as $squadra) {
            $nomisquadre[] = [
                'id' => $squadra,
                'nome' => Competizione::getArticleTitleById($squadra),
            ];
        }

        // Ordina l'array per il nome
        usort($nomisquadre, function ($a, $b) {
            return strcmp($a['nome'], $b['nome']);
        });

        // Estrai solo gli ID in un array separato
        $idsOrdinati = array_column($nomisquadre, 'id');

        return $idsOrdinati;
    }

    public static function getTablePartite($ID)
    {
        // Recupera le giornate dalla competizione
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $tablePartite = $prefix . 'competizione' . $ID . '_partite';
        return $tablePartite;
    }
    public static function getTableStatistiche($ID)
    {
        // Recupera le giornate dalla competizione
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $getTableStatistiche = $prefix . 'competizione' . $ID . '_statistiche';
        return $getTableStatistiche;
    }

    public static function setCompetizioneFinita($id)
    {
        $db = Factory::getDbo();
        $query = "UPDATE #__competizioni SET finita = 1 WHERE id = " . (int) $id;
        $db->setQuery($query);
        $db->execute();
        return;
    }

    public static function setCompetizionenonFinita($id)
    {
        $db = Factory::getDbo();
        $query = "UPDATE #__competizioni SET finita = 0 WHERE id = " . (int) $id;
        $db->setQuery($query);
        $db->execute();
        return;
    }

    public static function calculateStatistics($squadra, $view, $ar, $tablePartite)
    {
        $squadraID = $punti = $giocate = $vinte = $pari = $perse = $golFatti = $golSubiti = $differenza = 0;

        if ($view === 'casa') {
            $punti = ($squadra->VC * 3) + $squadra->NC;
            $giocate = $squadra->VC + $squadra->NC + $squadra->PC;
            $vinte = $squadra->VC;
            $pari = $squadra->NC;
            $perse = $squadra->PC;
            $golFatti = $squadra->GFC;
            $golSubiti = $squadra->GSC;
        } elseif ($view === 'trasferta') {
            $punti = ($squadra->VT * 3) + $squadra->NT;
            $giocate = $squadra->VT + $squadra->NT + $squadra->PT;
            $vinte = $squadra->VT;
            $pari = $squadra->NT;
            $perse = $squadra->PT;
            $golFatti = $squadra->GFT;
            $golSubiti = $squadra->GST;
        } elseif ($view === 'andata') {
            if ($ar === 0) {
                $punti = (($squadra->VC + $squadra->VT) * 3) + ($squadra->NC + $squadra->NT);
                $giocate = $squadra->VC + $squadra->VT + $squadra->NC + $squadra->NT + $squadra->PC + $squadra->PT;
                $vinte = $squadra->VC + $squadra->VT;
                $pari = $squadra->NC + $squadra->NT;
                $perse = $squadra->PC + $squadra->PT;
                $golFatti = $squadra->GFC + $squadra->GFT;
                $golSubiti = $squadra->GSC + $squadra->GST;
            } elseif ($ar === 1) {
                $squadraID = $squadra->ID; // Accedi all'ID della squadra
                $punti = ($squadra->V * 3) + $squadra->N; // Calcola i punti
                $giocate = $squadra->V + $squadra->N + $squadra->P; // Partite giocate
                $vinte = $squadra->V; // Partite vinte
                $pari = $squadra->N; // Partite pareggiate
                $perse = $squadra->P; // Partite perse
                $golFatti = $squadra->GF; // Gol fatti
                $golSubiti = $squadra->GS; // Gol subiti

            }
        } elseif ($view === 'ritorno') {
            if ($ar === 0) {
                $punti = 0;
                $giocate = 0;
                $vinte = 0;
                $pari = 0;
                $perse = 0;
                $golFatti = 0;
                $golSubiti = 0;
            } elseif ($ar === 1) {
                $squadraID = $squadra->ID; // Accedi all'ID della squadra
                $punti = ($squadra->V * 3) + $squadra->N; // Calcola i punti
                $giocate = $squadra->V + $squadra->N + $squadra->P; // Partite giocate
                $vinte = $squadra->V; // Partite vinte
                $pari = $squadra->N; // Partite pareggiate
                $perse = $squadra->P; // Partite perse
                $golFatti = $squadra->GF; // Gol fatti
                $golSubiti = $squadra->GS; // Gol subiti

            }
        } elseif ($view === 'totale' || $view === 'gironi') {
            $punti = (($squadra->VC + $squadra->VT) * 3) + ($squadra->NC + $squadra->NT);
            $giocate = $squadra->VC + $squadra->VT + $squadra->NC + $squadra->NT + $squadra->PC + $squadra->PT;
            $vinte = $squadra->VC + $squadra->VT;
            $pari = $squadra->NC + $squadra->NT;
            $perse = $squadra->PC + $squadra->PT;
            $golFatti = $squadra->GFC + $squadra->GFT;
            $golSubiti = $squadra->GSC + $squadra->GST;
        }

        $differenza = $golFatti - $golSubiti;
        $girone = self::getGironeBySquadraId($squadraID, $tablePartite);
        return [
            'squadra' => $squadraID,
            'punti' => $punti,
            'giocate' => $giocate,
            'vinte' => $vinte,
            'pari' => $pari,
            'perse' => $perse,
            'golFatti' => $golFatti,
            'golSubiti' => $golSubiti,
            'differenza' => $differenza,
            'girone' => $girone,
        ];
    }

    public static function getStats($tableStatistiche, $squadra = 0)
    {
        // Ottieni l'oggetto database di Joomla
        $db = Factory::getDbo();

        // Crea una nuova query
        $query = $db->getQuery(true);
        if ($squadra == 0) {
            $query->select('*')
                ->from($db->quoteName($tableStatistiche));
        } else {

            // Scrivi la query per selezionare tutti i dati dalla tabella 'statistiche'
            $query->select('*')
                ->from($tableStatistiche)
                ->where($db->quoteName('squadra') . " = " . $squadra); // Nota che Joomla prevede il prefisso '#__' per la tabella, che verrà sostituito dal prefisso effettivo del database

        }
        // Imposta la query e eseguila

        $db->setQuery($query);

        // Recupera i risultati come array di oggetti
        return $db->loadObjectList();
    }

    public static function getAndamento($tablePartite)
    {
        // Inizializza un array per tenere traccia dei punti accumulati per ogni squadra
        $andamento = [];

        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere le partite
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite))
            ->order('giornata ASC'); // Assicurati di ordinare per giornata

        // Esegui la query
        $db->setQuery($query);
        $partite = $db->loadObjectList();

        // Trova il numero massimo di giornate giocate
        $maxGiornata = max(array_column($partite, 'giornata'));

        // Inizializza il punteggio cumulativo
        foreach ($partite as $partita) {
            $giornata = $partita->giornata;
            $squadraCasa = $partita->squadra1;
            $squadraTrasferta = $partita->squadra2;
            $golCasa = $partita->gol1;
            $golTrasferta = $partita->gol2;

            // Salta le partite non giocate (gol null)
            if ($golCasa === null || $golTrasferta === null) {
                continue;
            }

            // Inizializza le squadre se non già presente
            if (!isset($andamento[$squadraCasa])) {
                $andamento[$squadraCasa] = [
                    'squadra' => $squadraCasa,
                    'risultati' => array_fill(1, $maxGiornata, 0) // Imposta 0 per ogni giornata
                ];
            }
            if (!isset($andamento[$squadraTrasferta])) {
                $andamento[$squadraTrasferta] = [
                    'squadra' => $squadraTrasferta,
                    'risultati' => array_fill(1, $maxGiornata, 0) // Imposta 0 per ogni giornata
                ];
            }

            // Calcola i punti per la giornata
            $puntiCasa = 0;
            $puntiTrasferta = 0;

            if ($golCasa > $golTrasferta) {
                $puntiCasa = 3; // Vittoria per la squadra di casa
            } elseif ($golCasa < $golTrasferta) {
                $puntiTrasferta = 3; // Vittoria per la squadra in trasferta
            } else {
                $puntiCasa = 1; // Pareggio
                $puntiTrasferta = 1; // Pareggio
            }

            // Accumula i punti per la giornata
            $andamento[$squadraCasa]['risultati'][$giornata] = $puntiCasa;
            $andamento[$squadraTrasferta]['risultati'][$giornata] = $puntiTrasferta;
        }

        // Ora propaghiamo i punti accumulati per le giornate non giocate e sommiamo
        foreach ($andamento as &$squadra) {
            $puntiAccum = 0; // Inizializza i punti accumulati
            for ($g = 1; $g <= $maxGiornata; $g++) {
                // Somma i punti accumulati fino a questa giornata
                $puntiAccum += $squadra['risultati'][$g];
                $squadra['risultati'][$g] = $puntiAccum; // Aggiorna il totale per questa giornata
            }
        }

        // Ritorna l'andamento calcolato
        return $andamento;
    }

    public static function getGiornate($tablePartite)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere il numero massimo di giornate
        $query = $db->getQuery(true)
            ->select('MAX(giornata) AS max_giornata')
            ->from($db->quoteName($tablePartite));

        // Esegui la query
        $db->setQuery($query);
        $maxGiornate = $db->loadResult();

        // Ora puoi usare $maxGiornate come numero di giornate
        return $maxGiornate;
    }

    public static function getPartite($tablePartite)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere i risultati delle partite
        $query = $db->getQuery(true)
            ->select('squadra1, squadra2, gol1, gol2, giornata, girone')
            ->from($db->quoteName($tablePartite))
            ->order('giornata');

        // Esegui la query
        $db->setQuery($query);

        try {
            return $db->loadObjectList(); // Restituisce un array di oggetti con i risultati delle partite
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle partite: ' . $e->getMessage();
            return [];
        }
    }

    // Funzione per controllare se tutti i gol sono NULL
    public static function checkGolNull($tablePartite)
    {
        $db = Factory::getDbo();

        // Query per verificare se tutti i gol sono NULL
        $query = $db->getQuery(true)
            ->select('COUNT(*) AS total')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('gol1') . ' IS NOT NULL OR ' . $db->quoteName('gol2') . ' IS NOT NULL');

        $db->setQuery($query);

        try {
            $result = $db->loadResult();
        } catch (Exception $e) {
            echo 'Errore durante il controllo dei gol: ' . $e->getMessage();
            return false;
        }

        return $result == 0; // Ritorna true se tutti i gol sono NULL
    }

    public static function abbreviaNomeSquadra($nome)
    {
        // Rimuovi eventuali spazi bianchi iniziali e finali
        $nome = trim($nome);
        // Dividi il nome in parole
        $parole = explode(' ', $nome);

        if (count($parole) === 1) {
            // Se c'è una sola parola, prendi le prime 3 lettere
            return substr($nome, 0, 3);
        } elseif (count($parole) >= 2) {
            // Se ci sono due o più parole, prendi 2 lettere dalla prima e 1 dalla seconda
            return substr($parole[0], 0, 2) . strtoupper(substr($parole[1], 0, 1));
        }

        return ''; // Restituisci una stringa vuota se non ci sono parole
    }
    public static function getPartitePerSquadra($squadraId, $tablePartite)
    {
        // Get a database connection
        $db = Factory::getDbo();

        // Create a query to fetch matches for the specified team
        $query = $db->getQuery(true)
            ->select('*') // Select all fields, adjust as necessary
            ->from($db->quoteName($tablePartite)) // Replace with your actual table name
            ->where($db->quoteName('squadra1') . ' = ' . (int) $squadraId . ' OR ' .
                $db->quoteName('squadra2') . ' = ' . (int) $squadraId) // Check both team columns
            ->order($db->quoteName('giornata') . ' ASC'); // Replace 'giornata' with your actual column name for matchday

        // Set the query and load the results
        $db->setQuery($query);
        $matches = $db->loadObjectList();

        return $matches; // Return the list of matches
    }

    public static function getPartitePerSquadraCasa($squadraId, $tablePartite)
    {
        // Get a database connection
        $db = Factory::getDbo();

        // Create a query to fetch matches for the specified team
        $query = $db->getQuery(true)
            ->select('*') // Select all fields, adjust as necessary
            ->from($db->quoteName($tablePartite)) // Replace with your actual table name
            ->where($db->quoteName('squadra1') . ' = ' . (int) $squadraId)
            ->order($db->quoteName('giornata') . ' ASC'); // Replace 'giornata' with your actual column name for matchday

        // Set the query and load the results
        $db->setQuery($query);
        $matches = $db->loadObjectList();

        return $matches; // Return the list of matches
    }

    public static function getPartitePerSquadraTrasferta($squadraId, $tablePartite)
    {
        // Get a database connection
        $db = Factory::getDbo();

        // Create a query to fetch matches for the specified team
        $query = $db->getQuery(true)
            ->select('*') // Select all fields, adjust as necessary
            ->from($db->quoteName($tablePartite)) // Replace with your actual table name
            ->where($db->quoteName('squadra2') . ' = ' . (int) $squadraId)
            ->order($db->quoteName('giornata') . ' ASC'); // Replace 'giornata' with your actual column name for matchday

        // Set the query and load the results
        $db->setQuery($query);
        $matches = $db->loadObjectList();

        return $matches; // Return the list of matches
    }

    public static function getGeneral($tablePartite, $tableStatistiche, $i)
    {
        if ($i === 0) {
            return number_format(self::getNumeroPartite($tablePartite), 0, '', '.');
        } elseif ($i === 1) {
            $tot = 0;
            $partite = self::getPartite($tablePartite);
            $numpartite = self::getNumeroPartite($tablePartite);
            foreach ($partite as $partita) {
                $tot += $partita->gol1 + $partita->gol2;
            }
            if ($numpartite === 0)
                $golxincontro = 0;
            else
                $golxincontro = round($tot / $numpartite, 2);
            return number_format($tot, 0, '', '.') . " (" . $golxincontro . " per incontro)";
        } else {
            // Ottieni l'oggetto di connessione al database di Joomla
            $db = Factory::getDbo();

            // Costruisci la query
            $query = $db->getQuery(true)
                ->select('*') // Seleziona tutte le colonne
                ->from($db->quoteName($tableStatistiche)); // La tabella è dinamica, quindi la gestiamo come variabile

            // Esegui la query
            $db->setQuery($query);

            // Ottieni i risultati come array associativo
            $stats = $db->loadAssocList(); // o puoi usare loadObjectList() se preferisci oggetti

            // Inizializzazione delle variabili per massimo e minimo
            $maxVittorie = -1;
            $minVittorie = PHP_INT_MAX;
            $squadreMaxVittorie = [];
            $squadreMinVittorie = [];

            $maxPareggi = -1;
            $minPareggi = PHP_INT_MAX;
            $squadreMaxPareggi = [];
            $squadreMinPareggi = [];

            $maxSconfitte = -1;
            $minSconfitte = PHP_INT_MAX;
            $squadreMaxSconfitte = [];
            $squadreMinSconfitte = [];

            $maxGolFatti = -1;
            $minGolFatti = PHP_INT_MAX;
            $squadreMaxGolFatti = [];
            $squadreMinGolFatti = [];

            $maxGolSubiti = -1;
            $minGolSubiti = PHP_INT_MAX;
            $squadreMaxGolSubiti = [];
            $squadreMinGolSubiti = [];

            $maxDiff = -1;
            $minDiff = PHP_INT_MAX;
            $squadreMaxDiff = [];
            $squadreMinDiff = [];

            // Itera su tutte le squadre per calcolare i record
            foreach ($stats as $squadra) {
                // Somma dei valori per ogni squadra
                $v = $squadra['VC'] + $squadra['VT'];
                $n = $squadra['NC'] + $squadra['NT'];
                $p = $squadra['PC'] + $squadra['PT'];
                $gf = $squadra['GFC'] + $squadra['GFT'];
                $gs = $squadra['GSC'] + $squadra['GST'];
                $diff = $gf - $gs;

                // Gestione dei massimi e minimi per ciascun parametro
                switch ($i) {
                    case 2: // Max vittorie
                        if ($v > $maxVittorie) {
                            $maxVittorie = $v;
                            $squadreMaxVittorie = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($v === $maxVittorie) {
                            $squadreMaxVittorie[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 3: // Min vittorie
                        if ($v < $minVittorie) {
                            $minVittorie = $v;
                            $squadreMinVittorie = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($v === $minVittorie) {
                            $squadreMinVittorie[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 4: // Max pareggi
                        if ($n > $maxPareggi) {
                            $maxPareggi = $n;
                            $squadreMaxPareggi = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($n === $maxPareggi) {
                            $squadreMaxPareggi[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 5: // Min pareggi
                        if ($n < $minPareggi) {
                            $minPareggi = $n;
                            $squadreMinPareggi = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($n === $minPareggi) {
                            $squadreMinPareggi[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 6: // Max sconfitte
                        if ($p > $maxSconfitte) {
                            $maxSconfitte = $p;
                            $squadreMaxSconfitte = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($p === $maxSconfitte) {
                            $squadreMaxSconfitte[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 7: // Min sconfitte
                        if ($p < $minSconfitte) {
                            $minSconfitte = $p;
                            $squadreMinSconfitte = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($p === $minSconfitte) {
                            $squadreMinSconfitte[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 8: // Max gol fatti
                        if ($gf > $maxGolFatti) {
                            $maxGolFatti = $gf;
                            $squadreMaxGolFatti = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($gf === $maxGolFatti) {
                            $squadreMaxGolFatti[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 9: // Min gol fatti
                        if ($gf < $minGolFatti) {
                            $minGolFatti = $gf;
                            $squadreMinGolFatti = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($gf === $minGolFatti) {
                            $squadreMinGolFatti[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 10: // Min gol subiti
                        if ($gs < $minGolSubiti) {
                            $minGolSubiti = $gs;
                            $squadreMinGolSubiti = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($gs === $minGolSubiti) {
                            $squadreMinGolSubiti[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 11: // Max gol subiti
                        if ($gs > $maxGolSubiti) {
                            $maxGolSubiti = $gs;
                            $squadreMaxGolSubiti = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($gs === $maxGolSubiti) {
                            $squadreMaxGolSubiti[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 12: // Max differenza gol
                        if ($diff > $maxDiff) {
                            $maxDiff = $diff;
                            $squadreMaxDiff = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($diff === $maxDiff) {
                            $squadreMaxDiff[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;

                    case 13: // Min differenza gol
                        if ($diff < $minDiff) {
                            $minDiff = $diff;
                            $squadreMinDiff = [self::getArticleTitleById($squadra['squadra'])];
                        } elseif ($diff === $minDiff) {
                            $squadreMinDiff[] = self::getArticleTitleById($squadra['squadra']);
                        }
                        break;
                }
            }

            // Restituire i risultati in base al parametro $i
            switch ($i) {
                case 2: // Max vittorie
                    return $maxVittorie . ": " . implode(', ', $squadreMaxVittorie);
                case 3: // Min vittorie
                    return $minVittorie . ": " . implode(', ', $squadreMinVittorie);
                case 4: // Max pareggi
                    return $maxPareggi . ": " . implode(', ', $squadreMaxPareggi);
                case 5: // Min pareggi
                    return $minPareggi . ": " . implode(', ', $squadreMinPareggi);
                case 6: // Max sconfitte
                    return $maxSconfitte . ": " . implode(', ', $squadreMaxSconfitte);
                case 7: // Min sconfitte
                    return $minSconfitte . ": " . implode(', ', $squadreMinSconfitte);
                case 8: // Max gol fatti
                    return $maxGolFatti . ": " . implode(', ', $squadreMaxGolFatti);
                case 9: // Min gol fatti
                    return $minGolFatti . ": " . implode(', ', $squadreMinGolFatti);
                case 10: // Min gol subiti
                    return $minGolSubiti . ": " . implode(', ', $squadreMinGolSubiti);
                case 11: // Max gol subiti
                    return $maxGolSubiti . ": " . implode(', ', $squadreMaxGolSubiti);
                case 12: // Max differenza gol
                    return $maxDiff . ": " . implode(', ', $squadreMaxDiff);
                case 13: // Min differenza gol
                    return $minDiff . ": " . implode(', ', $squadreMinDiff);
                default:
                    return 'Parametro non valido';
            }
        }
    }

    public static function getRecord($squadre, $tablePartite, $index, $mod)
    {
        $maxCount = 0;
        $records = []; // Array per memorizzare i record massimi

        foreach ($squadre as $squadra) {
            if ($mod === 70)
                $girone = " - " . self::getGironeBySquadraId($squadra, $tablePartite) . "º";
            else
                $girone = null;
            $stringa = self::getRecordIndividual($squadra, $tablePartite, $index);
            if (is_null($stringa))
                continue;
            $result = explode(":", $stringa);

            $count = (int) $result[0];
            $resto = trim($result[1] ?? ""); // Gestione del caso in cui il risultato sia vuoto
            $squadraName = self::getArticleTitleById($squadra);
            if ($index >= 0 && $index < 9) {
                if ($count > $maxCount) {
                    // Aggiorna il massimo e resetta l'array dei record
                    $maxCount = $count;
                    $records = [
                        "{$squadraName} {$resto}{$girone}"
                    ];
                } elseif ($count === $maxCount) {
                    // Aggiungi l'elemento al record corrente in caso di parità
                    $records[] = "{$squadraName} {$resto}{$girone}";
                }
            } elseif ($index >= 9 && $index < 12) {
                if ($count > $maxCount) {
                    // Aggiorna il massimo e resetta l'array dei record
                    $maxCount = $count;
                    $resto = $resto . $girone;
                    $resto = str_replace("<br>", "", $resto);
                    $records = explode(", ", $resto); // Separa i valori di $resto in base alle virgole e li inserisce nell'array, sostituendo le virgole con <br>
                } elseif ($count === $maxCount) {
                    $resto = $resto . $girone;
                    $resto = str_replace("<br>", "", $resto);
                    // Aggiungi l'elemento o gli elementi al record corrente in caso di parità
                    $additionalRecords = explode(", ", $resto); // Separa i valori in base alle virgole, sostituendo le virgole con <br>
                    foreach ($additionalRecords as $record) {
                        $records[] = $record; // Aggiungi ogni elemento esploso al record corrente
                    }
                }
            }
        }
        // Restituisci i risultati come stringa unita da linee
        if ($index >= 0 && $index < 9) {
            if ($maxCount === 0)
                return;
            return $maxCount . ": " . implode("<br>", $records);
        } elseif ($index >= 9 && $index < 12) {
            if ($maxCount === 0)
                return;
            $records = array_unique($records); // Rimuovi duplicati
            // Unisci il conteggio con il primo record e poi vai a capo per i restanti
            $result = "{$maxCount}: " . array_shift($records) . "<br>" . implode("<br>", $records);
            return $result; // Restituisci il risultato finale
        }

    }

    public static function getRecordIndividual($squadra, $tablePartite, $index)
    {
        $matches = self::getPartitePerSquadra($squadra, $tablePartite);
        $count = $giornataInizio = $giornataFine = 0;
        $maxcount = 0;
        $maxSequences = []; // Array per tenere traccia delle sequenze massime
        $partiteScartoMax = []; // Array per salvare le partite con margine massimo

        if ($index == 0 || $index == 1 || $index == 2)
            $matches = self::getPartitePerSquadra($squadra, $tablePartite);
        elseif ($index == 3 || $index == 4 || $index == 5)
            $matches = self::getPartitePerSquadraCasa($squadra, $tablePartite);
        elseif ($index == 6 || $index == 7 || $index == 8)
            $matches = self::getPartitePerSquadraTrasferta($squadra, $tablePartite);


        // Helper function to check if the condition is met
        $checkCondition = function ($match, $squadra, $index) {
            switch ($index) {
                case 0:
                    return ($squadra == $match->squadra1 && $match->gol1 > $match->gol2) || ($squadra == $match->squadra2 && $match->gol2 > $match->gol1);
                case 1:
                    return ($squadra == $match->squadra1 && $match->gol1 == $match->gol2 && $match->gol1 !== null && $match->gol2 !== null) || ($squadra == $match->squadra2 && $match->gol2 == $match->gol1 && $match->gol1 !== null && $match->gol2 !== null);
                case 2:
                    return ($squadra == $match->squadra1 && $match->gol1 < $match->gol2) || ($squadra == $match->squadra2 && $match->gol2 < $match->gol1);
                case 3:
                    return ($squadra == $match->squadra1 && $match->gol1 > $match->gol2);
                case 4:
                    return ($squadra == $match->squadra1 && $match->gol1 == $match->gol2 && $match->gol1 !== null && $match->gol2 !== null);
                case 5:
                    return ($squadra == $match->squadra1 && $match->gol1 < $match->gol2);
                case 6:
                    return ($squadra == $match->squadra2 && $match->gol1 < $match->gol2);
                case 7:
                    return ($squadra == $match->squadra2 && $match->gol1 == $match->gol2 && $match->gol1 !== null && $match->gol2 !== null);
                case 8:
                    return ($squadra == $match->squadra2 && $match->gol1 > $match->gol2);
                case 9:
                    return ($squadra == $match->squadra1 && $match->gol1 > $match->gol2) || ($squadra == $match->squadra2 && $match->gol2 > $match->gol1);
                case 10:
                    return ($squadra == $match->squadra1 && $match->gol1 < $match->gol2) || ($squadra == $match->squadra2 && $match->gol2 < $match->gol1);
                case 11:
                    return ($squadra == $match->squadra1 || $squadra == $match->squadra2);
                default:
                    return false;
            }
        };

        foreach ($matches as $match) {
            if ($index <= 8) {
                if ($checkCondition($match, $squadra, $index)) {
                    $count++;
                    if ($giornataInizio == 0) {
                        $giornataInizio = $match->giornata;
                    }
                    $giornataFine = $match->giornata;
                } else {

                    // Verifica se la sequenza corrente è la massima
                    if ($count > $maxcount) {
                        $maxcount = $count;
                        $maxSequences = [['inizio' => $giornataInizio, 'fine' => $giornataFine]]; // Reset delle sequenze
                    } elseif ($count == $maxcount) {
                        // Aggiungi la sequenza se ha la stessa lunghezza massima
                        $maxSequences[] = ['inizio' => $giornataInizio, 'fine' => $giornataFine];
                    }

                    // Reset dei contatori
                    $giornataInizio = $giornataFine = $count = 0;

                }
            } elseif ($index == 9 || $index == 10) {
                if ($checkCondition($match, $squadra, $index)) {
                    $scartoGol = abs($match->gol1 - $match->gol2); // Calcola lo scarto di gol
                    if ($scartoGol > $count) { // Nuovo massimo scarto di gol
                        $count = $scartoGol;
                        $partiteScartoMax = []; // Resetta l'array per il nuovo massimo scarto
                        $partiteScartoMax[] = [
                            'giornata' => $match->giornata,
                            'gol1' => $match->gol1,
                            'gol2' => $match->gol2,
                            'squadra1' => $match->squadra1,
                            'squadra2' => $match->squadra2
                        ];
                    } elseif ($scartoGol == $count) { // Stesso scarto massimo già visto
                        $partiteScartoMax[] = [
                            'giornata' => $match->giornata,
                            'gol1' => $match->gol1,
                            'gol2' => $match->gol2,
                            'squadra1' => $match->squadra1,
                            'squadra2' => $match->squadra2
                        ];
                    }
                }
            } elseif ($index == 11) {
                if ($checkCondition($match, $squadra, $index)) {
                    $sommaGol = $match->gol1 + $match->gol2;
                    if ($sommaGol > $count) { // Nuovo massimo scarto di gol
                        $count = $sommaGol;
                        $partiteScartoMax = []; // Resetta l'array per il nuovo massimo scarto
                        $partiteScartoMax[] = [
                            'giornata' => $match->giornata,
                            'gol1' => $match->gol1,
                            'gol2' => $match->gol2,
                            'squadra1' => $match->squadra1,
                            'squadra2' => $match->squadra2
                        ];
                    } elseif ($sommaGol == $count) { // Stesso scarto massimo già visto
                        $partiteScartoMax[] = [
                            'giornata' => $match->giornata,
                            'gol1' => $match->gol1,
                            'gol2' => $match->gol2,
                            'squadra1' => $match->squadra1,
                            'squadra2' => $match->squadra2
                        ];
                    }
                }
            }
        }

        if ($index <= 8) {
            // Verifica finale per l'ultima sequenza
            if ($count > $maxcount) {
                $maxcount = $count;
                $maxSequences = [['inizio' => $giornataInizio, 'fine' => $giornataFine]]; // Reset delle sequenze
            } elseif ($count == $maxcount) {
                $maxSequences[] = ['inizio' => $giornataInizio, 'fine' => $giornataFine];
            }

            if ($maxcount == 0)
                return;
            elseif ($maxcount == 1) {
                // Formatta il risultato
                $sequencesStr = implode(', ', array_map(function ($seq) {
                    return "{$seq['inizio']}º";
                }, $maxSequences));
            } else {
                // Formatta il risultato
                $sequencesStr = implode(', ', array_map(function ($seq) {
                    return "{$seq['inizio']}º-{$seq['fine']}º";
                }, $maxSequences));
            }

            return $maxcount . ': (' . $sequencesStr . ')';
        } elseif ($index == 9 || $index == 10) {
            $resultsByScarto = [];
            foreach ($partiteScartoMax as $partita) {
                $scarto = abs($partita['gol1'] - $partita['gol2']);
                $s1 = self::getArticleTitleById($partita['squadra1']);
                $s2 = self::getArticleTitleById($partita['squadra2']);
                $giornata = $partita['giornata'];

                // Raggruppa partite per scarto
                $resultsByScarto[$scarto][] = "{$s1} - {$s2} {$partita['gol1']}-{$partita['gol2']} ({$giornata}º)";
            }

            // Crea la stringa finale
            $result = "";
            foreach ($resultsByScarto as $scarto => $partite) {
                $result .= "$scarto: " . implode(", ", $partite) . "<br>";
            }

            return $result === "" ? "" : $result;
        } elseif ($index == 11) {
            $resultsBySomma = [];
            foreach ($partiteScartoMax as $partita) {
                $somma = $partita['gol1'] + $partita['gol2'];
                $s1 = self::getArticleTitleById($partita['squadra1']);
                $s2 = self::getArticleTitleById($partita['squadra2']);
                $giornata = $partita['giornata'];

                // Raggruppa partite per somma
                $resultsBySomma[$somma][] = "{$s1} - {$s2} {$partita['gol1']}-{$partita['gol2']} ({$giornata}º)";
            }

            // Crea la stringa finale
            $result = "";
            foreach ($resultsBySomma as $somma => $partite) {
                if ($somma === 0)
                    continue;
                $result .= "$somma: " . implode(", ", $partite) . "<br>";
            }

            return $result === "" ? "" : $result;
        }

    }

    public static function getUltimaPartita($tablePartite)
    {
        // Ottieni l'oggetto database
        $db = Factory::getDbo();

        // Crea la query per ottenere l'ultima partita
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' = (SELECT MAX(' . $db->quoteName('giornata') . ') FROM ' . $db->quoteName($tablePartite) . ')')
            ->where($db->quoteName('gol1') . ' IS NOT NULL')
            ->where($db->quoteName('gol2') . ' IS NOT NULL')
            ->where('(SELECT COUNT(*) FROM ' . $db->quoteName($tablePartite) . ' WHERE ' . $db->quoteName('giornata') . ' = (SELECT MAX(' . $db->quoteName('giornata') . ') FROM ' . $db->quoteName($tablePartite) . ')) = 1');

        // Esegui la query
        $db->setQuery($query);
        $partita = $db->loadObject();

        return $partita; // Restituisce l'oggetto partita o null se non ci sono risultati
    }

    public static function isFinale($tablePartite)
    {
        $db = Factory::getDbo();

        // Costruisce la query per contare le partite nell'ultima giornata
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' = (SELECT MAX(giornata) FROM ' . $db->quoteName($tablePartite) . ')');

        // Esegui la query
        $db->setQuery($query);
        $numeroPartiteMaxGiornata = $db->loadResult();

        // Restituisce true se c'è solo una partita nell'ultima giornata, altrimenti false
        return $numeroPartiteMaxGiornata === 1;
    }

    public static function getGironeBySquadraId($idSquadra, $tablePartite)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select($db->quoteName('girone'))
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('squadra1') . ' = ' . (int) $idSquadra . ' OR ' . $db->quoteName('squadra2') . ' = ' . (int) $idSquadra)
            ->setLimit(1); // Limita il risultato a un solo record, assumendo che una squadra appartenga a un solo girone

        $db->setQuery($query);
        return $db->loadResult();
    }

    public static function getClassificaGironi($tableStatistiche, $girone)
    {
        $db = Factory::getDbo();

        // Query per ottenere tutte le statistiche e calcolare i punti, la differenza reti e i gol fatti
        $query = $db->getQuery(true)
            ->select('*')
            ->select('
            (COALESCE(VC, 0) + COALESCE(VT, 0)) * 3 + (COALESCE(NC, 0) + COALESCE(NT, 0)) AS punti,  
            (COALESCE(GFC, 0) + COALESCE(GFT, 0) - COALESCE(GSC, 0) - COALESCE(GST, 0)) AS diff_reti, 
            (COALESCE(GFC, 0) + COALESCE(GFT, 0)) AS gol_fatti')
            ->from($db->quoteName($tableStatistiche))
            ->where($db->quoteName('girone') . " = " . $girone)
            ->order('punti DESC, diff_reti DESC, gol_fatti DESC, squadra ASC'); // Ordina secondo i criteri specificati

        $db->setQuery($query);

        try {
            return $db->loadObjectList(); // Restituisce un array di oggetti
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle statistiche: ' . $e->getMessage();
            return [];
        }
    }

    public static function getSquadrePerGirone($tablePartite, $girone)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere gli ID delle squadre senza duplicati
        $query = $db->getQuery(true)
            ->select('DISTINCT squadra1 AS squadra')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('girone') . ' = ' . $db->quote($girone))
            ->union(
                $db->getQuery(true)
                    ->select('DISTINCT squadra2 AS squadra')
                    ->from($db->quoteName($tablePartite))
                    ->where($db->quoteName('girone') . ' = ' . $db->quote($girone))
            );

        // Esegui la query
        $db->setQuery($query);

        try {
            // Restituisce un array di oggetti con tutte le squadre uniche del girone specificato
            return $db->loadObjectList();
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle squadre: ' . $e->getMessage();
            return [];
        }
    }

    public static function CreaFaseFinale($id, $user, $tableStatistiche)
    {
        $com = self::getCompetizioneById($id, $user);
        $mod = $com->modalita;
        $ar = $com->andata_ritorno;
        $nome = $com->nome_competizione;
        $gironi = $com->gironi;
        $ff = $com->fase_finale;
        $finita = $com->finita;
        $squadreperfasefinale = $ff / $gironi;


        $passati = []; // Array per contenere i valori di 'squadra'

        if ($mod === 70 && $finita === 1) {
            // Array temporaneo per raggruppare i valori per posizione
            $passatiPerPosizione = [];

            // Itera per ogni girone
            for ($i = 1; $i <= $gironi; $i++) {
                // Ottieni la classifica per il girone corrente
                $array = self::getClassificaGironi($tableStatistiche, $i);

                // Verifica che $array non sia vuoto
                if (!empty($array)) {
                    // Itera attraverso le posizioni del girone
                    foreach ($array as $posizione => $valore) {
                        if ($posizione >= $squadreperfasefinale) {
                            break; // Interrompi se superi il limite per la fase finale
                        }

                        // Assicurati che le proprietà 'squadra' e 'punti' esistano
                        if (isset($valore->squadra, $valore->punti)) {
                            // Raggruppa per posizione nei gironi con punti
                            $passatiPerPosizione[] = [
                                'id' => $valore->squadra,
                                'punti' => $valore->punti,
                                'posizione' => $posizione + 1, // Considera le posizioni da 1 in poi
                            ];
                        }
                    }
                }
            }

            // Ordina l'array per posizione e poi per punti
            usort($passatiPerPosizione, function ($a, $b) {
                // Ordina prima per posizione crescente
                if ($a['posizione'] != $b['posizione']) {
                    return $a['posizione'] - $b['posizione'];
                }
                // Se posizione uguale, ordina per punti decrescente
                return $b['punti'] - $a['punti'];
            });

            // Estrai solo gli ID delle squadre ordinate
            $passati = array_column($passatiPerPosizione, 'id');
        }
        self::TabellaFaseFinale($passati, $nome, $user, $ar);
    }

    public static function TabellaFaseFinale($squadre, $nome, $user, $ar)
    {
        //sort($squadre);
        $squadrenew = array_map('strval', $squadre);
        $data = array(
            'user_id' => $user, // ID dell'utente
            'nome_competizione' => $nome . " - Fase Finale", // Nome della competizione
            'modalita' => 69, // Modalità
            'tipo' => 71, // Categoria
            'gironi' => 0, // Numero di gironi
            'squadre' => $squadrenew, // ID delle squadre
            'andata_ritorno' => $ar, // Modalità andata/ritorno
            'partecipanti' => count($squadre), // Numero di partecipanti
            'fase_finale' => 0, // Stato fase finale
            'finita' => 0, // Stato finita
        );

        self::insertCompetizione($data);
    }

    public static function CheckNome($nome)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();

        // Crea un oggetto di query
        $query = $db->getQuery(true);

        // Seleziona il conteggio delle competizioni con il nome specificato
        $query->select('COUNT(*)')
            ->from($db->quoteName('#__competizioni')) // Sostituisci con il nome corretto della tua tabella
            ->where($db->quoteName('nome_competizione') . ' = ' . $db->quote($nome)); // Condizione per il nome

        // Imposta la query
        $db->setQuery($query);

        // Ottieni il conteggio
        $count = $db->loadResult();

        // Ritorna true se esiste almeno una competizione con quel nome, altrimenti false
        return $count > 0;
    }

    public static function getAllCompetizioni($squadraId, $user, $mod)
    {

        // Ottieni il database
        $db = Factory::getDbo();
        if ($mod === 0) {
            // Crea la query
            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__competizioni'))
                ->where($db->quoteName('finita') . " = 1")
                ->order($db->quoteName('creazione') . ' DESC');
        } else {
            // Crea la query
            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__competizioni'))
                ->where($db->quoteName('finita') . " = 1")
                ->where($db->quoteName('modalita') . " = " . $mod)
                ->order($db->quoteName('creazione') . ' DESC');
        }


        // Esegui la query
        $db->setQuery($query);

        try {
            $competizioni = $db->loadColumn(); // Restituisce un array con gli ID delle competizioni
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle competizioni: ' . $e->getMessage();
            return [];
        }
        $c = [];
        foreach ($competizioni as $competizione) {
            $comp = self::getCompetizioneById($competizione, $user);
            $squadre = [];
            if (!is_null($comp) && !is_null($comp->squadre)) {
                $squadre = json_decode($comp->squadre, true);
            }

            if (is_array($squadre) && in_array($squadraId, $squadre)) {
                $c[] = $competizione;
            }

        }
        return $c;
    }

    public static function getScontriDiretti($squadra1, $squadra2, $luogo, $modalita, $user)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per trovare le partite tra le due squadre
        if ($modalita === 0) {
            $query = $db->getQuery(true)
                ->select('*') // Seleziona tutte le competizioni
                ->from($db->quoteName('#__competizioni'))
                ->where($db->quoteName('user_id') . ' = ' . $user)
                ->where($db->quoteName('finita') . ' = 1')
                ->order($db->quoteName('creazione') . ' DESC');
        } else {
            // Crea la query per ottenere tutte le competizioni
            $query = $db->getQuery(true)
                ->select('*') // Seleziona tutte le competizioni
                ->from($db->quoteName('#__competizioni'))
                ->where($db->quoteName('user_id') . ' = ' . $user)
                ->where($db->quoteName('finita') . ' = 1')
                ->where($db->quoteName('modalita') . ' = ' . $modalita)
                ->order($db->quoteName('creazione') . ' DESC');
        }
        // Esegui la query
        $db->setQuery($query);

        try {
            $comp = $db->loadObjectList();
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle partite: ' . $e->getMessage();
            return [];
        }
        $scontriDiretti = [];
        foreach ($comp as $c) {
            $tablePartite = self::getTablePartite($c->id);
            $partite = array_reverse(self::getPartite($tablePartite));
            foreach ($partite as $partita) {
                if ($luogo === 0) {
                    // Controlla se la partita coinvolge squadra1 e squadra2
                    if (
                        ($partita->squadra1 == $squadra1 && $partita->squadra2 == $squadra2) ||
                        ($partita->squadra1 == $squadra2 && $partita->squadra2 == $squadra1)
                    ) {
                        // Aggiungi la partita agli scontri diretti
                        $scontriDiretti[] = [
                            'partita' => $partita,
                            'competizione' => $c->nome_competizione,
                            'id' => $c->id,
                        ];
                    }
                } elseif ($luogo === 1) {
                    // Controlla se la partita coinvolge squadra1 e squadra2
                    if ($partita->squadra1 == $squadra1 && $partita->squadra2 == $squadra2) {
                        // Aggiungi la partita agli scontri diretti
                        $scontriDiretti[] = [
                            'partita' => $partita,
                            'competizione' => $c->nome_competizione,
                            'id' => $c->id,
                        ];
                    }
                } elseif ($luogo === 2) {
                    // Controlla se la partita coinvolge squadra1 e squadra2
                    if ($partita->squadra1 == $squadra2 && $partita->squadra2 == $squadra1) {
                        // Aggiungi la partita agli scontri diretti
                        $scontriDiretti[] = [
                            'partita' => $partita,
                            'competizione' => $c->nome_competizione,
                            'id' => $c->id,
                        ];
                    }
                }
            }
        }
        return $scontriDiretti;
    }

    public static function ris($forza1, $forza2)
    {
        // Generiamo potenza casuale in base alla forza delle due squadre
        $pw1 = rand(0, $forza1);
        $pw2 = rand(0, $forza2);
        $diff = $pw1 - $pw2;
        // Inizializziamo i gol
        $gol1 = 0;
        $gol2 = 0;
        $fdiff = abs($forza1 - $forza2);
        // Calcoliamo i gol in base alla forza e alla casualità
        if ($fdiff > 100) {
            if ($diff >= 1000) {
                $gol1 = self::pesoRand(3, 7);
                $gol2 = self::pesoRand(0, $gol1 - 3);
            } elseif ($diff >= 500) {
                $gol1 = self::pesoRand(2, 6);
                $gol2 = self::pesoRand(0, $gol1 - 2);
            } elseif ($diff >= 250) {
                $gol1 = self::pesoRand(1, 5);
                $gol2 = self::pesoRand(0, $gol1 - 1);
            } elseif ($diff >= 100) {
                $gol1 = self::pesoRand(0, 4);
                $gol2 = self::pesoRand(0, $gol1);
            } elseif ($diff >= 50) {
                $gol1 = self::pesoRand(0, 3);
                $gol2 = self::pesoRand(0, $gol1 + 1);
            } elseif ($diff >= -50) {
                $gol1 = self::pesoRand(0, 3);
                $gol2 = self::pesoRand(0, 3);
            } elseif ($diff >= -150) {
                $gol2 = self::pesoRand(0, 3);
                $gol1 = self::pesoRand(0, $gol2 + 1);
            } elseif ($diff >= -350) {
                $gol2 = self::pesoRand(0, 4);
                $gol1 = self::pesoRand(0, $gol2);
            } elseif ($diff >= -600) {
                $gol2 = self::pesoRand(1, 5);
                $gol1 = self::pesoRand(0, $gol2 - 1);
            } elseif ($diff >= -1200) {
                $gol2 = self::pesoRand(2, 6);
                $gol1 = self::pesoRand(0, $gol2 - 2);
            } else {
                $gol2 = self::pesoRand(3, 7);
                $gol1 = self::pesoRand(0, $gol2 - 3);
            }
        } else {
            if ($diff >= 50) {
                $gol1 = self::pesoRand(2, 6);
                $gol2 = self::pesoRand(0, $gol1 - 2);
            } elseif ($diff >= 15) {
                $gol1 = self::pesoRand(1, 4);
                $gol2 = self::pesoRand(0, $gol1);
            } elseif ($diff >= -15) {
                $gol1 = self::pesoRand(0, 3);
                $gol2 = self::pesoRand(0, 3);
            } elseif ($diff >= -50) {
                $gol2 = self::pesoRand(1, 4);
                $gol1 = self::pesoRand(0, $gol2);
            } else {
                $gol2 = self::pesoRand(2, 6);
                $gol1 = self::pesoRand(0, $gol2 - 2);
            }
        }
        // Restituisce il risultato finale
        return [
            'squadra1' => $gol1,
            'squadra2' => $gol2
        ];
    }

    public static function pesoRand($min, $max)
    {
        // Imposta pesi per risultati bassi con piccola probabilità di risultati alti
        $pesi = [
            0 => 50,
            1 => 60,
            2 => 40,
            3 => 25,
            4 => 12,
            5 => 8,
            6 => 4,
            7 => 1
        ];

        // Filtra i pesi per l'intervallo desiderato
        $pesiFiltrati = array_filter($pesi, function ($k) use ($min, $max) {
            return $k >= $min && $k <= $max;
        }, ARRAY_FILTER_USE_KEY);

        // Calcola la somma dei pesi filtrati
        $sommaPesi = array_sum($pesiFiltrati);

        // Genera un numero casuale tra 0 e la somma dei pesi
        $random = rand(0, $sommaPesi - 1);

        // Seleziona un numero in base ai pesi
        $soglia = 0;
        foreach ($pesiFiltrati as $numero => $peso) {
            $soglia += $peso;
            if ($random < $soglia) {
                return $numero;
            }
        }
    }

    public static function checkWinner($tablePartite, $tableStatistiche, $squadra, $mod)
    {
        if ($mod === 68) {
            $classifica = self::getClassifica($tableStatistiche);
            if ($squadra == $classifica[0]->squadra)
                return true;
        } elseif ($mod === 69) {
            $classifica = self::getUltimaPartita($tablePartite);
            if ($classifica->gol1 > $classifica->gol2) {
                if ($classifica->squadra1 == $squadra)
                    return true;
            } elseif ($classifica->gol1 < $classifica->gol2) {
                if ($classifica->squadra2 == $squadra)
                    return true;
            }
        }
        return false;
    }

    public static function checkTop($tablePartite, $tableStatistiche, $number)
    {
        $classifica = self::getClassifica($tableStatistiche);
        $top = array_slice($classifica, 0, $number);
        return $top;
    }

    public static function deleteArticleById($articleId, $userId)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Recupera tutte le competizioni e controlla se l'ID dell'articolo è presente nella colonna "squadre"
        $query = $db->getQuery(true)
            ->select($db->quoteName('squadre'))
            ->from($db->quoteName('#__competizioni')); // Tabella competizioni, adattato al tuo schema

        // Esegui la query per ottenere le competizioni
        $db->setQuery($query);
        $competizioni = $db->loadColumn(); // Ottiene tutte le voci della colonna "squadre"

        // Verifica se l'articolo è associato a una competizione
        foreach ($competizioni as $squadreJson) {
            $squadre = json_decode($squadreJson, true); // Decodifica il JSON in un array
            if (in_array((int) $articleId, $squadre)) {
                // Se l'articolo è presente in una competizione, non lo eliminare
                return false; // Restituisce false se l'articolo è collegato a una competizione
            }
        }

        // Se l'articolo non è presente in nessuna competizione, procedi con l'eliminazione

        // Crea la query per eliminare l'articolo solo se l'utente loggato è il creatore
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__content')) // Tabella degli articoli in Joomla
            ->where([
                $db->quoteName('id') . ' = ' . (int) $articleId,
                $db->quoteName('created_by') . ' = ' . (int) $userId
            ]);

        // Esegui la query per eliminare l'articolo
        $db->setQuery($query);

        try {
            // Esegui l'eliminazione dell'articolo
            $db->execute();

            // Se l'articolo è stato eliminato, procediamo a rimuovere i relativi campi
            if ($db->getAffectedRows() > 0) {
                // Rimuovi i relativi campi dalla tabella #__fields_values
                $deleteFieldsQuery = $db->getQuery(true)
                    ->delete($db->quoteName('#__fields_values')) // Tabella dei valori dei campi
                    ->where($db->quoteName('item_id') . ' = ' . (int) $articleId);

                // Esegui la query per eliminare i campi
                $db->setQuery($deleteFieldsQuery);
                $db->execute();

                return true; // Restituisce true se l'articolo e i campi sono stati eliminati
            }

            return false; // L'articolo non è stato eliminato
        } catch (Exception $e) {
            return false; // Errore durante l'esecuzione
        }
    }

    public static function getsquadramancante($tablePartite, $giornata, $squadre)
    {
        // Ottieni il database
        $db = Factory::getDbo();
        $squadre = array_map('intval', json_decode($squadre, true));
        // Recupera tutte le competizioni e controlla se l'ID dell'articolo è presente nella colonna "squadre"
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite))
            ->where($db->quoteName('giornata') . ' = ' . (int) $giornata); // Tabella competizioni, adattato al tuo schema

        // Esegui la query per ottenere le competizioni
        $db->setQuery($query);
        $partite = $db->loadObjectList(); // Ottiene tutte le voci della colonna "squadre"
        $squad = [];
        foreach ($partite as $partita) {
            $squad[] = $partita->squadra1;
            $squad[] = $partita->squadra2;
        }
        // Trova l'elemento che manca
        $mancante = array_diff($squadre, $squad);

        // Il risultato sarà un array, quindi prendi il primo elemento
        $mancanteSquadra = reset($mancante);

        return $mancanteSquadra;  // Mostra il numero mancante

    }


}
