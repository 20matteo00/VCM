<?php
if (isset($_GET['mod'])) {
    $mod = $_GET['mod'];
    $user = $_SESSION['username'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nomeCampionato = $_POST['nome_campionato'];
        $andataRitorno = $_POST['andata_ritorno'];
        $numeroPartecipanti = $_POST['numero_partecipanti'];
        if (isset($_POST['gironi'])) {
            $gironi = $_POST['gironi'];
        } else {
            $gironi = 0;
        }
        if (isset($_POST['numero_partecipanti_fasefinale'])) {
            $fasefinale = $_POST['numero_partecipanti_fasefinale'];
        } else {
            $fasefinale = 0;
        }

        if ($mod == 1) {
            $moda = "campionato";
            if (!is_numeric($andataRitorno) || !is_numeric($numeroPartecipanti) || ($andataRitorno != 1 && $andataRitorno != 0) || ($numeroPartecipanti < 2 || $numeroPartecipanti > 32 || $numeroPartecipanti % 2 != 0)) {
                header("Location: index.php?page=home");
                exit();
            }
        } elseif ($mod == 2) {
            $moda = "eliminazione";
            if (!is_numeric($andataRitorno) || !is_numeric($numeroPartecipanti) || ($andataRitorno != 1 && $andataRitorno != 0) || ($numeroPartecipanti < 2 || $numeroPartecipanti > 128)) {
                header("Location: index.php?page=home");
                exit();
            }
        } elseif ($mod == 3) {
            $moda = "champions";
            if (!is_numeric($andataRitorno) || !is_numeric($numeroPartecipanti) || ($andataRitorno != 1 && $andataRitorno != 0) || ($numeroPartecipanti < 8 || $numeroPartecipanti > 128) || ($gironi < 2 || $gironi > 8)) {
                header("Location: index.php?page=home");
                exit();
            }
        }

        echo '<div class="container mt-5 w-50 text-center">
            <h1>' . $VCM_select_players . '</h1>
            <form id="playerSelectionForm" action="#" method="post">
                <input type="hidden" name="andata_ritorno" value="' . $andataRitorno . '">
                <input type="hidden" name="modalita" value="' . $moda . '">
                <input type="hidden" name="nome_campionato" value="' . $nomeCampionato . '">
                <input type="hidden" name="numero_partecipanti" value="' . $numeroPartecipanti . '">
                <input type="hidden" name="numero_partecipanti_fasefinale" value="' . $fasefinale . '">
                <input type="hidden" name="gironi" value="' . $gironi . '">
                <h3>' . $VCM_choose . ' ' . $numeroPartecipanti . ' ' . $VCM_players . '</h3>';

        // Ottenere l'elenco delle squadre dal database
        $sql = "SELECT * FROM squadre WHERE utente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $nomeSquadra = $row['nome'];
                $i++;
                echo "<div class='form-check border'>
                    <input class='form-check-input player-checkbox' type='checkbox' id='squadra$i' name='giocatori_selezionati[]' value='$nomeSquadra' onclick='limitCheckboxes($numeroPartecipanti)'>
                    <label class='form-check-label partecipants' for='squadra$i'>$nomeSquadra</label>
                  </div>";
            }
        } else {
            echo "Nessuna squadra trovata.";
        }

        echo '<button type="submit" name="select_players" class="btn btn-primary my-3" disabled>Conferma Selezione</button>
          </form>
          </div>';

        $stmt->close();
    } else { // Genera il form HTML
        if ($mod == 1) {
            echo '<div class="container mt-5 w-25 text-center">
            <h1>' . $VCM_league . '</h1>
            <form action="" method="post">
                <div class="form-group mt-3">
                    <label for="' . $VCM_name . '">' . $VCM_name . ':</label>
                    <input type="text" class="form-control" id="nome_campionato" name="nome_campionato" required>
                </div>
                <div class="form-group mt-3">
                    <label for="' . $VCM_round_trip . '">' . $VCM_round_trip . ':</label>
                    <select class="form-control" id="andata_ritorno" name="andata_ritorno" required>
                        <option value="1">' . $VCM_yes . '</option>
                        <option value="0">' . $VCM_no . '</option>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="' . $VCM_partecipants . '">' . $VCM_partecipants . ':</label>
                    <input type="number" class="form-control" id="numero_partecipanti" name="numero_partecipanti" min="2" max="32" required>
                </div>
                <button type="submit" class="btn btn-primary my-3">' . $VCM_send . '</button>
            </form>
        </div>';
        } elseif ($mod == 2) {
            // Genera il form HTML
            echo '<div class="container mt-5 w-25 text-center">
            <h1>' . $VCM_elimination . '</h1>
            <form action="" method="post">
                <div class="form-group mt-3">
                    <label for="' . $VCM_name . '">' . $VCM_name . ':</label>
                    <input type="text" class="form-control" id="nome_campionato" name="nome_campionato" required>
                </div>
                <div class="form-group mt-3">
                    <label for="' . $VCM_round_trip . '">' . $VCM_round_trip . ':</label>
                    <select class="form-control" id="andata_ritorno" name="andata_ritorno" required>
                        <option value="1">' . $VCM_yes . '</option>
                        <option value="0">' . $VCM_no . '</option>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="' . $VCM_partecipants . '">' . $VCM_partecipants . ':</label>
                    <select class="form-control" id="numero_partecipanti" name="numero_partecipanti" required>
                        <option value="2">2</option>
                        <option value="4">4</option>
                        <option value="8">8</option>
                        <option value="16">16</option>
                        <option value="32">32</option>
                        <option value="64">64</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary my-3">' . $VCM_send . '</button>
            </form>
            </div>';
        } elseif ($mod == 3) {
            echo '<div class="container mt-5 w-25 text-center">
                <h1>' . $VCM_champions . '</h1>
                <form action="" method="post">
                    <div class="form-group mt-3">
                        <label for="' . $VCM_name . '">' . $VCM_name . ':</label>
                        <input type="text" class="form-control" id="nome_campionato" name="nome_campionato" required>
                    </div>
                    <div class="form-group mt-3">
                        <label for="' . $VCM_round_trip . '">' . $VCM_round_trip . ':</label>
                        <select class="form-control" id="andata_ritorno" name="andata_ritorno" required>
                            <option value="1">' . $VCM_yes . '</option>
                            <option value="0">' . $VCM_no . '</option>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="' . $VCM_groups . '">' . $VCM_groups . ':</label>
                        <select class="form-control" id="gironi" name="gironi" required>
                            <option value="8">8</option>
                            <option value="4">4</option>
                            <option value="2">2</option>

                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="' . $VCM_partecipants . '">' . $VCM_partecipants . ':</label>
                        <select class="form-control" id="numero_partecipanti" name="numero_partecipanti" required>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="fase_finale">Fase Finale:</label>
                        <select class="form-control" id="numero_partecipanti_fasefinale" name="numero_partecipanti_fasefinale" required>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary my-3">' . $VCM_send . '</button>
                </form>
            </div>';
        }
    }
}



// Gestione della logica di inserimento nel database al momento della sottomissione del modulo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_players'])) {
    $user = $_SESSION['username']; // Assicurati di ottenere l'utente dalla sessione o dal form
    $nomeCampionato = $_POST['nome_campionato'];
    $moda = $_POST['modalita'];
    $andataRitorno = $_POST['andata_ritorno'];
    $numeroPartecipanti = $_POST['numero_partecipanti'];
    $gironi = $_POST['gironi'];
    $fasefinale = $_POST['numero_partecipanti_fasefinale'];
    $giocatoriSelezionati = $_POST['giocatori_selezionati'];
    $finita=0;
    // Ordina l'array in modo casuale
    shuffle($giocatoriSelezionati);

    if (count($giocatoriSelezionati) != $numeroPartecipanti) {
        echo $VCM_error2 . $numeroPartecipanti . " " . $VCM_players;
        exit();
    }
    // Unire l'array $giocatoriSelezionati in una stringa separata da virgole
    $giocatoriSelezionatiStringa = implode(",", $giocatoriSelezionati);

    // Prepara la query SQL per inserire i dati nella tabella competizioni
    $sql_insert = "INSERT INTO competizioni (utente, nome, modalita, gironi, ar, partecipanti, fasefinale, finita, squadre) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssiiiiis", $user, $nomeCampionato, $moda, $gironi, $andataRitorno, $numeroPartecipanti, $fasefinale, $finita, $giocatoriSelezionatiStringa);

    // Esegui la query e controlla il risultato
    if ($stmt_insert->execute()) {
        header("Location: index.php?page=competizioni&com=0");
        exit();
    } else {
        echo $VCM_error1 . $stmt_insert->error;
    }

    // Chiusura dello statement preparato
    $stmt_insert->close();
}
