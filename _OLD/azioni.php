<div class="container">

    <?php
    $user = $_SESSION['username'];

    // Verifica che i parametri necessari siano stati passati correttamente
    if (isset($_GET['name']) && isset($_GET['squadra'])) {
        $name = $_GET['name'];
        $squadra = $_GET['squadra'];

        if ($name == "eliminasquadra") {

            $check = "SELECT * FROM competizioni where utente = ?";
            $stmt = $conn->prepare($check);
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();
            $squadre = array();
            while ($row = $result->fetch_assoc()) {
                $squadre = explode(",", $row['squadre']);
            }

            $stmt->close();
            if (in_array($squadra, $squadre)) {
                header("Location: index.php?page=giocatori");
                exit();
            } else {
                // Eliminazione della squadra
                $sql_delete = "DELETE FROM squadre WHERE utente = ? AND nome = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("ss", $user, $squadra);

                if ($stmt_delete->execute()) {
                    header("Location: index.php?page=giocatori");
                    exit();
                } else {
                    echo $VCM_delete_error1 . $stmt_delete->error;
                }

                // Chiusura dello statement preparato
                $stmt_delete->close();
            }
        }

        if ($name == "modificasquadra") {
            if (isset($_POST['submit_modifica'])) {
                $nuovo_nome = $_POST['nome'];
                $forza = $_POST['forza'];
                $colore_primario = $_POST['colore_primario'];
                $colore_secondario = $_POST['colore_secondario'];
                $gruppo = $_POST['gruppo'];

                // Query SQL preparata per l'aggiornamento
                $sql_update = "UPDATE squadre SET nome=?, forza=?, gruppo=?, colore1=?, colore2=? WHERE utente=? AND nome=?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sisssss", $nuovo_nome, $forza, $gruppo, $colore_primario, $colore_secondario, $user, $squadra);

                if ($stmt_update->execute()) {
                    header("Location: index.php?page=giocatori");
                    exit();
                } else {
                    echo $VCM_edit_error . $stmt_update->error;
                }

                // Chiusura dello statement preparato
                $stmt_update->close();
            }

            // Seleziona i dati della squadra per il form di modifica
            $sql_select = "SELECT * FROM squadre WHERE utente = ? AND nome = ?";
            $stmt_select = $conn->prepare($sql_select);
            $stmt_select->bind_param("ss", $user, $squadra);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();

            if ($result_select->num_rows > 0) {
                $row = $result_select->fetch_assoc();
                $nome = $row['nome'];
                $forza = $row['forza'];
                $colore_primario = $row['colore1'];
                $colore_secondario = $row['colore2'];
                $gruppo = $row['gruppo'];
            } else {
                echo $VCM_query1_error . $squadra;
            }

            // Chiusura dello statement preparato
            $stmt_select->close();

            // Recupera i gruppi dell'utente corrente per popolare il campo select
            $query_gruppi = "SELECT nome FROM gruppi WHERE utente = ? ORDER BY nome";
            $stmt_gruppi = $conn->prepare($query_gruppi);
            $stmt_gruppi->bind_param("s", $user);
            $stmt_gruppi->execute();
            $result_gruppi = $stmt_gruppi->get_result();
            $stmt_gruppi->close();
        }
    ?>
        <!-- Form per la modifica -->

        <form action="index.php?page=azioni&name=modificasquadra&user=<?php echo $user; ?>&squadra=<?php echo urlencode($squadra); ?>" method="post">
            <input type="hidden" name="user" value="<?php echo htmlspecialchars($user); ?>">
            <input type="hidden" name="squadra" value="<?php echo htmlspecialchars($squadra); ?>">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_name; ?>" class="form-label"><?php echo $VCM_name; ?></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_strength; ?>" class="form-label"><?php echo $VCM_strength; ?></label>
                        <input type="number" class="form-control" id="forza" name="forza" value="<?php echo htmlspecialchars($forza); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="gruppo" class="form-label">Gruppo</label>
                        <select class="form-control" id="gruppo" name="gruppo" required>
                            <?php
                            while ($row = $result_gruppi->fetch_assoc()) :
                                $selected = ($row['nome'] === $gruppo) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($row['nome']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($row['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_color1; ?>" class="form-label"><?php echo $VCM_color1; ?></label>
                        <input type="color" class="form-control" id="colore_primario" name="colore_primario" value="<?php echo htmlspecialchars($colore_primario); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_color2; ?>" class="form-label"><?php echo $VCM_color2; ?></label>
                        <input type="color" class="form-control" id="colore_secondario" name="colore_secondario" value="<?php echo htmlspecialchars($colore_secondario); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-success w-100" name="submit_modifica"><?php echo $VCM_save; ?></button>
                </div>
            </div>
        </form>
    <?php
    }

    if (isset($_GET['name']) && isset($_GET['gruppo'])) {
        $name = $_GET['name'];
        $gruppo = $_GET['gruppo'];

        if ($name == "eliminagruppo") {
            // Eliminazione del gruppo
            $sql_delete = "DELETE FROM gruppi WHERE utente = ? AND nome = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ss", $user, $gruppo);

            if ($stmt_delete->execute()) {
                header("Location: index.php?page=gruppi");
                exit();
            } else {
                echo $VCM_delete_error1 . $stmt_delete->error;
            }

            // Chiusura dello statement preparato
            $stmt_delete->close();
        }

        if ($name == "modificagruppo") {
            if (isset($_POST['submit_modifica'])) {
                $nuovo_nome = $_POST['nome_gruppo'];
                $colore_primario = $_POST['colore_primario'];
                $colore_secondario = $_POST['colore_secondario'];

                // Query SQL preparata per l'aggiornamento
                $sql_update = "UPDATE gruppi SET nome=?, colore1=?, colore2=? WHERE utente=? AND nome=?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssss", $nuovo_nome, $colore_primario, $colore_secondario, $user, $gruppo);

                if ($stmt_update->execute()) {
                    header("Location: index.php?page=gruppi");
                    exit();
                } else {
                    echo $VCM_edit_error . $stmt_update->error;
                }

                // Chiusura dello statement preparato
                $stmt_update->close();
            }

            // Seleziona i dati del gruppo per il form di modifica
            $sql_select = "SELECT * FROM gruppi WHERE utente = ? AND nome = ?";
            $stmt_select = $conn->prepare($sql_select);
            $stmt_select->bind_param("ss", $user, $gruppo);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();

            if ($result_select->num_rows > 0) {
                $row = $result_select->fetch_assoc();
                $nome_gruppo = $row['nome'];
                $colore_primario = $row['colore1'];
                $colore_secondario = $row['colore2'];
            } else {
                echo $VCM_query1_error . $gruppo;
            }

            // Chiusura dello statement preparato
            $stmt_select->close();
        }
    ?>

        <!-- Form per la modifica -->
        <form action="index.php?page=azioni&name=modificagruppo&user=<?php echo $user; ?>&gruppo=<?php echo urlencode($gruppo); ?>" method="post">
            <input type="hidden" name="user" value="<?php echo htmlspecialchars($user); ?>">
            <input type="hidden" name="gruppo" value="<?php echo htmlspecialchars($gruppo); ?>">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_name; ?>" class="form-label"><?php echo $VCM_name; ?></label>
                        <input type="text" class="form-control" id="nome_gruppo" name="nome_gruppo" value="<?php echo htmlspecialchars($nome_gruppo); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_color1; ?>" class="form-label"><?php echo $VCM_color1; ?></label>
                        <input type="color" class="form-control" id="colore_primario" name="colore_primario" value="<?php echo htmlspecialchars($colore_primario); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_color2; ?>" class="form-label"><?php echo $VCM_color2; ?></label>
                        <input type="color" class="form-control" id="colore_secondario" name="colore_secondario" value="<?php echo htmlspecialchars($colore_secondario); ?>" required>
                    </div>
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-success w-100" name="submit_modifica"><?php echo $VCM_save; ?></button>
                </div>
            </div>
        </form>
        <?php

    }

    if (isset($_GET['name']) && isset($_GET['competizione']) && isset($_GET['com']) && isset($_GET['mod'])) {
        $name = $_GET['name'];
        $competizione = $_GET['competizione'];
        $com = $_GET['com'];
        $mod = $_GET['mod'];

        // Sanitize user and name
        $user_sanitized = sanitize_string($user);
        $name_sanitized = sanitize_string($competizione);

        // Nome della tabella
        $tablepartite = "{$user_sanitized}_{$name_sanitized}_partite";
        $tablestatistiche = "{$user_sanitized}_{$name_sanitized}_statistiche";

        if ($name == "eliminacompetizione") {
            // Elimina anche le tabelle correlate
            $sql_drop_partite = "DROP TABLE IF EXISTS $tablepartite";
            $sql_drop_statistiche = "DROP TABLE IF EXISTS $tablestatistiche";
            $conn->query($sql_drop_partite);
            $conn->query($sql_drop_statistiche);
            // Eliminazione della competizione
            $sql_delete = "DELETE FROM competizioni WHERE utente = ? AND nome = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ss", $user, $competizione);
            if ($stmt_delete->execute()) {
                header("Location: index.php?page=competizioni&com=" . $com);
                exit();
            } else {
                echo $VCM_delete_error2 . $stmt_delete->error;
            }

            // Chiusura dello statement preparato
            $stmt_delete->close();
        }

        if ($name == "visualizzacompetizione") {
            // Creazione della query per creare la tabella
            $sql1 = "CREATE TABLE IF NOT EXISTS $tablepartite (
            utente VARCHAR(100),
            nome VARCHAR(100),
            squadra1 VARCHAR(100),
            squadra2 VARCHAR(100),
            gol1 INT NULL,
            gol2 INT NULL,
            giornata INT NOT NULL,
            girone INT NULL,
            PRIMARY KEY (utente, nome, squadra1, squadra2),
            FOREIGN KEY (utente, nome) REFERENCES competizioni(utente, nome),
            FOREIGN KEY (utente, squadra1) REFERENCES squadre(utente, nome),
            FOREIGN KEY (utente, squadra2) REFERENCES squadre(utente, nome)
        )";
            $sql2 = "CREATE TABLE IF NOT EXISTS $tablestatistiche (
            utente VARCHAR(100),
            nome VARCHAR(100),
            squadra VARCHAR(100),
            vinte_casa INT NOT NULL,
            pari_casa INT NOT NULL,
            perse_casa INT NOT NULL,
            fatti_casa INT NOT NULL,
            subiti_casa INT NOT NULL,
            vinte_trasferta INT NOT NULL,
            pari_trasferta INT NOT NULL,
            perse_trasferta INT NOT NULL,
            fatti_trasferta INT NOT NULL,
            subiti_trasferta INT NOT NULL,
            girone INT NULL,
            PRIMARY KEY (utente, nome, squadra),
            FOREIGN KEY (utente, nome) REFERENCES competizioni(utente, nome),
            FOREIGN KEY (utente, squadra) REFERENCES squadre(utente, nome)
        )";
            // Esecuzione della query
            if ($conn->query($sql1) === FALSE || $conn->query($sql2) === FALSE) {
                header("Location: index.php?page=competizioni&com=0");
            }
			
            header("Location: index.php?page=visualizza&name=" . $competizione . "&mod=" . $mod . "&tabpar=" . $tablepartite . "&tabstat=" . $tablestatistiche);
            exit();
        }
    }

    if (isset($_GET['name']) && isset($_GET['competizione'])) {
        $competizione = $_GET['competizione'];
        $name = $_GET['name'];

        if ($name == "chiudi") {
            $sql = "UPDATE competizioni SET finita = 1 WHERE utente = ? AND nome = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $competizione);
            if ($stmt->execute()) {
                header("Location: index.php?page=competizioni&com=1");
                exit();
            }
        }
        if ($name == "riapri") {
            $sql = "UPDATE competizioni SET finita = 0 WHERE utente = ? AND nome = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $competizione);
            if ($stmt->execute()) {
                header("Location: index.php?page=competizioni&com=0");
                exit();
            }
        }
    }
    
    if (isset($_GET['azione']) && isset($_GET['giornata']) && isset($_GET['squadra1']) && isset($_GET['squadra2']) && isset($_GET['gol1']) && isset($_GET['gol2']) && isset($_GET['tabpar']) && isset($_GET['tabstat']) && isset($_GET['name']) && isset($_GET['mod'])) {
        $azione = $_GET['azione'];
        $giornata = $_GET['giornata'];
        $squadra1 = $_GET['squadra1'];
        $squadra2 = $_GET['squadra2'];
        $gol1 = $_GET['gol1'];
        $gol2 = $_GET['gol2'];
        $tablepartite = $_GET['tabpar'];
        $tablestatistiche = $_GET['tabstat'];
        $nome = $_GET['name'];
        $mod = $_GET['mod'];

        if ($azione == "salva") {
            $sql = "INSERT INTO $tablepartite (utente, nome, squadra1, squadra2, gol1, gol2, giornata) VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE gol1 = VALUES(gol1), gol2 = VALUES(gol2)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiii", $user, $nome, $squadra1, $squadra2, $gol1, $gol2, $giornata);
            if ($stmt->execute()) {
                header("Location: index.php?page=visualizza&name=" . $nome . "&mod=".$mod."&tabpar=" . $tablepartite . "&tabstat=" . $tablestatistiche . "#giornata" . $giornata);
                
                exit();
            }
        } elseif ($azione == "cancella"){
            $sql = "DELETE FROM $tablepartite WHERE utente = ? AND nome = ? AND squadra1 = ? AND squadra2 = ? AND giornata = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $user, $nome, $squadra1, $squadra2, $giornata);
            if ($stmt->execute()) {
                header("Location: index.php?page=visualizza&name=" . $nome . "&mod=".$mod."&tabpar=" . $tablepartite . "&tabstat=" . $tablestatistiche . "#giornata" . $giornata);
                exit();
            }
        }
    }

    if (isset($_GET['lang'])) {
        $lang = $_GET['lang'];
        $url = $_GET['url'];

        if ($lang == "en") {
            $_SESSION['lang'] = "en";
        } elseif ($lang == "it") {
            $_SESSION['lang'] = "it";
        }
        header("Location: $url");
        exit();
    }
        ?>




        <?php
        // Funzione per rimuovere i caratteri speciali e sostituirli con underscore
        function sanitize_string($input)
        {
            // Converti tutto in minuscole
            $input = strtolower($input);
            // Sostituisci tutti i caratteri non alfanumerici con underscore
            $input = preg_replace("/[^a-zA-Z0-9]/", "_", $input);
            // Sostituisci multipli underscore con un singolo underscore
            $input = preg_replace("/_+/", "_", $input);
            return $input;
        }
        ?>
</div>