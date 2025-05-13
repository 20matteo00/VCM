<?php
$user = $_SESSION['username'];
if (isset($_GET['name']) && isset($_GET['mod'])) {
    $name = $_GET['name'];
    $mod = $_GET['mod'];
    $ar = $_GET['ar'];
    $tablepartite = $_GET['tabpar'];
    $tablestatistiche = $_GET['tabstat'];
    $totpartite = $_GET['totpar'];
    if ($mod == "champions") {
        // Query per contare le partite e controllare i risultati
        $sql = "SELECT COUNT(*) AS total_matches, 
            COUNT(CASE WHEN gol1 IS NOT NULL AND gol2 IS NOT NULL THEN 1 END) AS non_null_results 
            FROM $tablepartite 
            WHERE utente = ? AND nome = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user, $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $totalMatches = $row['total_matches'];
        $nonNullResults = $row['non_null_results'];
        if ($nonNullResults == $totpartite) {
            $sql = "UPDATE competizioni SET finita = 1 WHERE utente = ? AND nome = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            $sql = "UPDATE competizioni SET finita = 0 WHERE utente = ? AND nome = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            header("location: index.php?page=visualizza&name=$name&mod=$mod&tabpar=$tablepartite&tabstat=$tablestatistiche");
            exit(); 
        }


        $sql = "SELECT * FROM competizioni WHERE utente=? AND nome=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user, $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $partecipanti = $row['partecipanti'];
        $gironi = $row['gironi'];
        $fasefinale = $row['fasefinale'];

        $limit = $fasefinale / $gironi;

        $squadrepassate = [];
        for ($i = 1; $i <= $gironi; $i++) {
            // Query per calcolare i punti, la differenza reti e ordinare i risultati
            $sql = "
        SELECT squadra, 
            (vinte_casa * 3 + pari_casa + vinte_trasferta * 3 + pari_trasferta) AS punti,
            (fatti_casa - subiti_casa + fatti_trasferta - subiti_trasferta) AS differenza_reti,
            (fatti_casa + fatti_trasferta) AS gol_fatti
        FROM $tablestatistiche
        WHERE utente = ? AND nome = ? AND girone = ?
        ORDER BY punti DESC, differenza_reti DESC, gol_fatti DESC, squadra ASC LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $user, $name, $i, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            while ($row = $result->fetch_assoc()) {
                $squadrepassate[] = $row['squadra'];
            }
        }

        $nomeCampionato = $name . " - Fase Finale";
        $moda = "eliminazione";
        $numeroPartecipanti = count($squadrepassate);
        $gironi = 0;
        $fasefinale = 0;
        $giocatoriSelezionati = $squadrepassate;
        $finita = 0;
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
        $stmt_insert->bind_param("sssiiiiis", $user, $nomeCampionato, $moda, $gironi, $ar, $numeroPartecipanti, $fasefinale, $finita, $giocatoriSelezionatiStringa);

        // Esegui la query e controlla il risultato
        if ($stmt_insert->execute()) {
            header("Location: index.php?page=competizioni&com=0");
            exit(); 
        } else {
            echo $VCM_error1 . $stmt_insert->error;
        }

        // Chiusura dello statement preparato
        $stmt_insert->close();
    } elseif($mod == "campionato" || $mod == "eliminazione"){
        // Query per contare le partite e controllare i risultati
        $sql = "SELECT COUNT(*) AS total_matches, 
            COUNT(CASE WHEN gol1 IS NOT NULL AND gol2 IS NOT NULL THEN 1 END) AS non_null_results 
            FROM $tablepartite 
            WHERE utente = ? AND nome = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user, $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $totalMatches = $row['total_matches'];
        $nonNullResults = $row['non_null_results'];

        if ($nonNullResults == $totpartite) {
            $sql = "UPDATE competizioni SET finita = 1 WHERE utente = ? AND nome = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            header("location: index.php?page=competizioni&com=0");
            exit();
        } else {
            $sql = "UPDATE competizioni SET finita = 0 WHERE utente = ? AND nome = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $name);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            header("location: index.php?page=visualizza&name=$name&mod=$mod&tabpar=$tablepartite&tabstat=$tablestatistiche&totpar=$totpartite&ar=$ar");
            exit();
        }
    }
}
