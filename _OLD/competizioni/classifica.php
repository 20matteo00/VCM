<?php
$user = $_SESSION['username'];
if (isset($_GET['name']) && isset($_GET['mod'])) {
    $name = $_GET['name'];
    $mod = $_GET['mod'];
    $tablepartite = $_GET['tabpar'];
    $tablestatistiche = $_GET['tabstat'];
    if (isset($_GET['type']))
        $type = $_GET['type'];

    $sql = "SELECT * FROM competizioni WHERE utente = ? AND nome = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $ar = $row['ar'];
    // Ottieni tutte le partite per l'utente
    $sql = "SELECT * FROM " . $tablepartite . " WHERE utente=? AND nome=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $name);
    $stmt->execute();
    $result = $stmt->get_result();

    // Inizializza un array per le statistiche
    $statistiche = [];

    while ($row = $result->fetch_assoc()) {
        $squadra1 = $row['squadra1'];
        $squadra2 = $row['squadra2'];
        $gol1 = $row['gol1'];
        $gol2 = $row['gol2'];
        $girone = $row['girone'];


        // Aggiorna statistiche per squadra 1
        if (!isset($statistiche[$squadra1])) {
            $statistiche[$squadra1] = [
                'vinte_casa' => 0,
                'pari_casa' => 0,
                'perse_casa' => 0,
                'fatti_casa' => 0,
                'subiti_casa' => 0,
                'vinte_trasferta' => 0,
                'pari_trasferta' => 0,
                'perse_trasferta' => 0,
                'fatti_trasferta' => 0,
                'subiti_trasferta' => 0,
                'girone' => $girone,
            ];

        }

        // Aggiorna statistiche per squadra 2
        if (!isset($statistiche[$squadra2])) {
            $statistiche[$squadra2] = [
                'vinte_casa' => 0,
                'pari_casa' => 0,
                'perse_casa' => 0,
                'fatti_casa' => 0,
                'subiti_casa' => 0,
                'vinte_trasferta' => 0,
                'pari_trasferta' => 0,
                'perse_trasferta' => 0,
                'fatti_trasferta' => 0,
                'subiti_trasferta' => 0,
                'girone' => $girone,
            ];

        }

        // Statistiche per squadra 1 (casa)
        $statistiche[$squadra1]['fatti_casa'] += $gol1;
        $statistiche[$squadra1]['subiti_casa'] += $gol2;
        // Statistiche per squadra 2 (trasferta)
        $statistiche[$squadra2]['fatti_trasferta'] += $gol2;
        $statistiche[$squadra2]['subiti_trasferta'] += $gol1;

        if ($gol1 > $gol2) {
            $statistiche[$squadra1]['vinte_casa']++;
            $statistiche[$squadra2]['perse_trasferta']++;
        } elseif ($gol1 == $gol2 && is_numeric($gol1) && is_numeric($gol2)) {

            $statistiche[$squadra1]['pari_casa']++;
            $statistiche[$squadra2]['pari_trasferta']++;
        } elseif ($gol1 < $gol2) {
            $statistiche[$squadra1]['perse_casa']++;
            $statistiche[$squadra2]['vinte_trasferta']++;
        }


    }

    $stmt->close();

    // Salva le statistiche nel database
    foreach ($statistiche as $squadra => $stats) {
        $sql_statistiche = "INSERT INTO $tablestatistiche (utente, nome, squadra, vinte_casa, pari_casa, perse_casa, fatti_casa, subiti_casa, vinte_trasferta, pari_trasferta, perse_trasferta, fatti_trasferta, subiti_trasferta, girone) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE vinte_casa=VALUES(vinte_casa), pari_casa=VALUES(pari_casa), perse_casa=VALUES(perse_casa), fatti_casa=VALUES(fatti_casa), subiti_casa=VALUES(subiti_casa), vinte_trasferta=VALUES(vinte_trasferta), pari_trasferta=VALUES(pari_trasferta), perse_trasferta=VALUES(perse_trasferta), fatti_trasferta=VALUES(fatti_trasferta), subiti_trasferta=VALUES(subiti_trasferta)";

        $stmt_statistiche = $conn->prepare($sql_statistiche);
        $stmt_statistiche->bind_param(
            "sssiiiiiiiiiii",
            $user,
            $name,
            $squadra,
            $stats['vinte_casa'],
            $stats['pari_casa'],
            $stats['perse_casa'],
            $stats['fatti_casa'],
            $stats['subiti_casa'],
            $stats['vinte_trasferta'],
            $stats['pari_trasferta'],
            $stats['perse_trasferta'],
            $stats['fatti_trasferta'],
            $stats['subiti_trasferta'],
            $stats['girone'],
        );

        $stmt_statistiche->execute();
        $stmt_statistiche->close();
    }

    if (isset($_GET['calcola']) && $_GET['calcola'] == 'calcola') {
        $totpar = $_GET['totpar'];

        header("location: index.php?page=fasefinale&name=$name&mod=$mod&tabpar=$tablepartite&tabstat=$tablestatistiche&totpar=$totpar&ar=$ar");
        exit();

    }
}

?>
<div class="container my-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header text-center">
                <h1><?php echo $name ?></h1>
            </div>
            <div class="card-body">
                <div class="table table-responsive">
                    <table class="table table-striped table-bordered text-center miatable" id="myTable">
                        <?php if (isset($_GET['type']) && $type == "general") { ?>
                            <thead>
                                <tr class="fw-bold">
                                    <?php if ($mod == "champions") {
                                        echo "<td colspan='3'>Classifica</td>";
                                    } else {
                                        echo "<td colspan='2'>Classifica</td>";
                                    } ?>

                                    <td colspan="8">Totale</td>
                                    <td colspan="8">Casa</td>
                                    <td colspan="8">Trasferta</td>
                                </tr>
                                <tr>
                                    <th id="sortById">#</th>
                                    <?php if ($mod == "champions") {
                                        echo "<th id='sortByRounds'>Girone</th>";
                                    } ?>
                                    <th id="sortByName">Squadre</th>
                                    <th id="sortByTotalPoints">Pt</th>
                                    <th id="sortByTotalGames">G</th>
                                    <th id="sortByTotalVictories">V</th>
                                    <th id="sortByTotalDraws">N</th>
                                    <th id="sortByTotalLosses">P</th>
                                    <th id="sortByTotalGoalsFor">GF</th>
                                    <th id="sortByTotalGoalsAgainst">GS</th>
                                    <th id="sortByTotalGoalDifference">DR</th>
                                    <th id="sortByTotalPointsHome">Pt</th>
                                    <th id="sortByTotalGamesHome">G</th>
                                    <th id="sortByTotalVictoriesHome">V</th>
                                    <th id="sortByTotalDrawsHome">N</th>
                                    <th id="sortByTotalLossesHome">P</th>
                                    <th id="sortByTotalGoalsForHome">GF</th>
                                    <th id="sortByTotalGoalsAgainstHome">GS</th>
                                    <th id="sortByTotalGoalDifferenceHome">DR</th>
                                    <th id="sortByTotalPointsAway">Pt</th>
                                    <th id="sortByTotalGamesAway">G</th>
                                    <th id="sortByTotalVictoriesAway">V</th>
                                    <th id="sortByTotalDrawsAway">N</th>
                                    <th id="sortByTotalLossesAway">P</th>
                                    <th id="sortByTotalGoalsForAway">GF</th>
                                    <th id="sortByTotalGoalsAgainstAway">GS</th>
                                    <th id="sortByTotalGoalDifferenceAway">DR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                /* $sql = "SELECT * FROM $tablestatistiche WHERE utente=? AND nome=?"; */
                                $sql = "SELECT *, 
                                            (vinte_casa * 3 + pari_casa + vinte_trasferta * 3 + pari_trasferta) AS punti,
                                            (fatti_casa - subiti_casa + fatti_trasferta - subiti_trasferta) AS differenza_reti,
                                            (fatti_casa + fatti_trasferta) AS gol_fatti
                                        FROM $tablestatistiche
                                        WHERE utente = ? AND nome = ?
                                        ORDER BY punti DESC, differenza_reti DESC, gol_fatti DESC, squadra ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ss", $user, $name);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $rank = 1;
                                while ($row = $result->fetch_assoc()) {
                                    $squadra = $row['squadra'];

                                    $vinte_casa = $row['vinte_casa'];
                                    $pari_casa = $row['pari_casa'];
                                    $perse_casa = $row['perse_casa'];
                                    $fatti_casa = $row['fatti_casa'];
                                    $subiti_casa = $row['subiti_casa'];
                                    $giocate_casa = $vinte_casa + $pari_casa + $perse_casa;
                                    $punti_casa = $vinte_casa * 3 + $pari_casa;
                                    $differenza_casa = $fatti_casa - $subiti_casa;

                                    $vinte_trasferta = $row['vinte_trasferta'];
                                    $pari_trasferta = $row['pari_trasferta'];
                                    $perse_trasferta = $row['perse_trasferta'];
                                    $fatti_trasferta = $row['fatti_trasferta'];
                                    $subiti_trasferta = $row['subiti_trasferta'];
                                    $giocate_trasferta = $vinte_trasferta + $pari_trasferta + $perse_trasferta;
                                    $punti_trasferta = $vinte_trasferta * 3 + $pari_trasferta;
                                    $differenza_trasferta = $fatti_trasferta - $subiti_trasferta;

                                    $vinte = $vinte_casa + $vinte_trasferta;
                                    $pari = $pari_casa + $pari_trasferta;
                                    $perse = $perse_casa + $perse_trasferta;
                                    $fatti = $fatti_casa + $fatti_trasferta;
                                    $subiti = $subiti_casa + $subiti_trasferta;
                                    $giocate = $giocate_casa + $giocate_trasferta;
                                    $punti = $punti_casa + $punti_trasferta;
                                    $differenza = $differenza_casa + $differenza_trasferta;

                                    $girone = $row['girone'];

                                    echo "<tr>";
                                    echo "<td>{$rank}</td>";
                                    if ($mod == "champions") {

                                        echo "<td>{$girone}</td>";
                                    }
                                    echo "<td>{$squadra}</td>";
                                    echo "<td>{$punti}</td>";
                                    echo "<td>{$giocate}</td>";
                                    echo "<td>{$vinte}</td>";
                                    echo "<td>{$pari}</td>";
                                    echo "<td>{$perse}</td>";
                                    echo "<td>{$fatti}</td>";
                                    echo "<td>{$subiti}</td>";
                                    echo "<td>{$differenza}</td>";
                                    echo "<td>{$punti_casa}</td>";
                                    echo "<td>{$giocate_casa}</td>";
                                    echo "<td>{$vinte_casa}</td>";
                                    echo "<td>{$pari_casa}</td>";
                                    echo "<td>{$perse_casa}</td>";
                                    echo "<td>{$fatti_casa}</td>";
                                    echo "<td>{$subiti_casa}</td>";
                                    echo "<td>{$differenza_casa}</td>";
                                    echo "<td>{$punti_trasferta}</td>";
                                    echo "<td>{$giocate_trasferta}</td>";
                                    echo "<td>{$vinte_trasferta}</td>";
                                    echo "<td>{$pari_trasferta}</td>";
                                    echo "<td>{$perse_trasferta}</td>";
                                    echo "<td>{$fatti_trasferta}</td>";
                                    echo "<td>{$subiti_trasferta}</td>";
                                    echo "<td>{$differenza_trasferta}</td>";
                                    echo "</tr>";
                                    $rank++;
                                }
                                ?>
                            </tbody>
                        <?php } elseif (isset($_GET['type']) && $type == "become") {
                            $sql = "SELECT squadra FROM {$tablestatistiche} WHERE utente = ? AND nome = ? ORDER BY squadra";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $user, $name);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $squadre = [];

                            while ($row = $result->fetch_assoc()) {
                                $squadre[] = $row['squadra'];
                            }
                            $stmt->close();
                            $stopgiornata = 0;
                            $sql = "SELECT COUNT(DISTINCT giornata) as cont FROM {$tablepartite} WHERE utente = ? AND nome = ? ORDER BY giornata";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $user, $name);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $cont = $row['cont'];

                            if ($ar == 0) {
                                $stopgiornata = $cont / 2;
                            }
                            echo "<thead><tr><th>Squadre</th>";
                            $sql = "SELECT DISTINCT giornata FROM {$tablepartite} WHERE utente = ? AND nome = ? order BY giornata";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $user, $name);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $k = 0;
                            while ($row = $result->fetch_assoc()) {
                                echo "<th>{$row['giornata']}</th>";
                                $k++;
                                if ($k == $stopgiornata)
                                    break;

                            }

                            echo "</tr></thead><tbody>";
                            foreach ($squadre as $squadra) {
                                $conteggio = 0;
                                $sql = "SELECT * FROM {$tablepartite} WHERE utente = ? AND nome = ? AND (squadra1 = ? OR squadra2 = ?) order BY giornata";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ssss", $user, $name, $squadra, $squadra);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $giornate = [];
                                echo "<tr><td>" . $squadra . "</td>";
                                while ($row = $result->fetch_assoc()) {
                                    $giornate[] = $row['giornata'];
                                    if ($squadra == $row['squadra1']) {
                                        if ($row['gol1'] > $row['gol2']) {
                                            $conteggio += 3;
                                            echo "<td>" . $conteggio . "</td>";
                                        } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {

                                            $conteggio += 1;
                                            echo "<td>" . $conteggio . "</td>";
                                        } else {
                                            $conteggio += 0;
                                            echo "<td>" . $conteggio . "</td>";
                                        }
                                    } elseif ($squadra == $row['squadra2']) {
                                        if ($row['gol2'] > $row['gol1']) {
                                            $conteggio += 3;
                                            echo "<td>" . $conteggio . "</td>";
                                        } elseif ($row['gol2'] == $row['gol1'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $conteggio += 1;
                                            echo "<td>" . $conteggio . "</td>";
                                        } else {
                                            $conteggio += 0;
                                            echo "<td>" . $conteggio . "</td>";
                                        }
                                    }
                                    $gg = $row['giornata'];
                                    if ($gg == $stopgiornata) {
                                        break;
                                    }
                                }

                                echo "</tr>";
                                $stmt->close();
                            }
                            echo "</tbody>";

                        } elseif (isset($_GET['type']) && (($type == "home") || ($type == "away"))) {
                            $sql = "SELECT squadra FROM {$tablestatistiche} WHERE utente = ? AND nome = ? ORDER BY squadra";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $user, $name);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $squadre = [];

                            while ($row = $result->fetch_assoc()) {
                                $squadre[] = $row['squadra'];
                            }
                            $stmt->close();

                            $puntiH = [];
                            $puntiA = [];
                            foreach ($squadre as $squadra) {
                                $conteggio = 0;
                                $sql = "SELECT * FROM {$tablepartite} WHERE utente = ? AND nome = ? AND (squadra1 = ? OR squadra2 = ?) order BY giornata";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ssss", $user, $name, $squadra, $squadra);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $puntiH[$squadra] = 0;
                                $puntiA[$squadra] = 0;
                                while ($row = $result->fetch_assoc()) {

                                    if ($squadra == $row['squadra1']) {
                                        if ($row['gol1'] > $row['gol2']) {
                                            $puntiH[$squadra] += 3;
                                        } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $puntiH[$squadra] += 1;
                                        } else {
                                            $puntiH[$squadra] += 0;
                                        }
                                    } elseif ($squadra == $row['squadra2']) {
                                        if ($row['gol2'] > $row['gol1']) {
                                            $puntiA[$squadra] += 3;
                                        } elseif ($row['gol2'] == $row['gol1'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $puntiA[$squadra] += 1;
                                        } else {
                                            $puntiA[$squadra] += 0;
                                        }
                                    }

                                }
                                $stmt->close();
                            }
                            ?>
                            <?php if ($type == "home") { ?>
                                <thead>
                                    <tr>
                                        <td colspan="2" class="fw-bold">CASA</td>
                                    </tr>
                                    <tr>
                                        <th>Squadre</th>
                                        <th>Punti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($squadre as $squadra) {
                                        echo "<tr><td>{$squadra}</td><td>{$puntiH[$squadra]}</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            <?php } elseif ($type == "away") { ?>
                                <thead>
                                    <tr>
                                        <td colspan="2" class="fw-bold">TRASFERTA</td>
                                    </tr>
                                    <tr>
                                        <th>Squadre</th>
                                        <th>Punti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($squadre as $squadra) {
                                        echo "<tr><td>{$squadra}</td><td>{$puntiA[$squadra]}</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            <?php } ?>
                            <?php

                        } elseif (isset($_GET['type']) && (($type == "firstround") || ($type == "returnround"))) {
                            $sql = "SELECT squadra FROM {$tablestatistiche} WHERE utente = ? AND nome = ? ORDER BY squadra";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $user, $name);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $squadre = [];

                            while ($row = $result->fetch_assoc()) {
                                $squadre[] = $row['squadra'];
                            }

                            $stmt->close();
                            $sql = "SELECT * FROM competizioni WHERE utente = ? AND nome = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $user, $name);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $gironi = $row['gironi'];
                            $partecipanti = $row['partecipanti'];

                            if ($mod == "campionato") {
                                $giornate = count($squadre) - 1;
                            } elseif ($mod == "champions") {
                                $giornate = $partecipanti / $gironi - 1;
                            }
                            $puntiFR = [];
                            $puntiRR = [];
                            foreach ($squadre as $squadra) {
                                $conteggio = 0;
                                $sql = "SELECT * FROM {$tablepartite} WHERE utente = ? AND nome = ? AND (squadra1 = ? OR squadra2 = ?) order BY giornata";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ssss", $user, $name, $squadra, $squadra);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $puntiFR[$squadra] = 0;
                                $puntiRR[$squadra] = 0;
                                while ($row = $result->fetch_assoc()) {

                                    if ($squadra == $row['squadra1'] && $row['giornata'] <= $giornate) {
                                        if ($row['gol1'] > $row['gol2']) {
                                            $puntiFR[$squadra] += 3;
                                        } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $puntiFR[$squadra] += 1;
                                        } else {
                                            $puntiFR[$squadra] += 0;
                                        }
                                    } elseif ($squadra == $row['squadra2'] && $row['giornata'] <= $giornate) {
                                        if ($row['gol2'] > $row['gol1']) {
                                            $puntiFR[$squadra] += 3;
                                        } elseif ($row['gol2'] == $row['gol1'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $puntiFR[$squadra] += 1;
                                        } else {
                                            $puntiFR[$squadra] += 0;
                                        }
                                    } elseif ($squadra == $row['squadra1'] && $row['giornata'] > $giornate) {
                                        if ($row['gol1'] > $row['gol2']) {
                                            $puntiRR[$squadra] += 3;
                                        } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $puntiRR[$squadra] += 1;
                                        } else {
                                            $puntiRR[$squadra] += 0;
                                        }
                                    } elseif ($squadra == $row['squadra2'] && $row['giornata'] > $giornate) {
                                        if ($row['gol2'] > $row['gol1']) {
                                            $puntiRR[$squadra] += 3;
                                        } elseif ($row['gol2'] == $row['gol1'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                            $puntiRR[$squadra] += 1;
                                        } else {
                                            $puntiRR[$squadra] += 0;
                                        }
                                    }

                                }
                                $stmt->close();
                            }
                            ?>
                            <?php if ($type == "firstround") { ?>
                                <thead>
                                    <tr>
                                        <td colspan="2" class="fw-bold">ANDATA</td>
                                    </tr>
                                    <tr>
                                        <th>Squadre</th>
                                        <th>Punti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($squadre as $squadra) {
                                        echo "<tr><td>{$squadra}</td><td>{$puntiFR[$squadra]}</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            <?php } elseif ($type == "returnround") { ?>
                                <thead>
                                    <tr>
                                        <td colspan="2" class="fw-bold">RITORNO</td>
                                    </tr>
                                    <tr>
                                        <th>Squadre</th>
                                        <th>Punti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($squadre as $squadra) {
                                        echo "<tr><td>{$squadra}</td><td>{$puntiRR[$squadra]}</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            <?php } ?>
                            <?php
                        } ?>
                    </table>
                    <?php
                    if (isset($_GET['type']) && ($type == "rounds")) { ?>
                        <?php
                        $sql = "SELECT * FROM competizioni WHERE utente = ? AND nome = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ss", $user, $name);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        $gironi = $row['gironi'];
                        for ($i = 1; $i <= $gironi; $i++) {
                            ?>
                            <table class="table table-striped table-bordered text-center miatable my-5 tablegironi"
                                id="myTable<?php echo $i ?>">
                                <p class="fw-bold text-center">Girone <?php echo $i ?></p>
                                <thead class='mb-5'>
                                    <tr class="fw-bold">
                                        <td colspan='2'>Classifica</td>
                                        <td colspan="8">Totale</td>
                                        <td colspan="8">Casa</td>
                                        <td colspan="8">Trasferta</td>
                                    </tr>
                                    <tr>
                                        <th id="sortById">#</th>
                                        <th id="sortByName">Squadre</th>
                                        <th id="sortByTotalPoints">Pt</th>
                                        <th id="sortByTotalGames">G</th>
                                        <th id="sortByTotalVictories">V</th>
                                        <th id="sortByTotalDraws">N</th>
                                        <th id="sortByTotalLosses">P</th>
                                        <th id="sortByTotalGoalsFor">GF</th>
                                        <th id="sortByTotalGoalsAgainst">GS</th>
                                        <th id="sortByTotalGoalDifference">DR</th>
                                        <th id="sortByTotalPointsHome">Pt</th>
                                        <th id="sortByTotalGamesHome">G</th>
                                        <th id="sortByTotalVictoriesHome">V</th>
                                        <th id="sortByTotalDrawsHome">N</th>
                                        <th id="sortByTotalLossesHome">P</th>
                                        <th id="sortByTotalGoalsForHome">GF</th>
                                        <th id="sortByTotalGoalsAgainstHome">GS</th>
                                        <th id="sortByTotalGoalDifferenceHome">DR</th>
                                        <th id="sortByTotalPointsAway">Pt</th>
                                        <th id="sortByTotalGamesAway">G</th>
                                        <th id="sortByTotalVictoriesAway">V</th>
                                        <th id="sortByTotalDrawsAway">N</th>
                                        <th id="sortByTotalLossesAway">P</th>
                                        <th id="sortByTotalGoalsForAway">GF</th>
                                        <th id="sortByTotalGoalsAgainstAway">GS</th>
                                        <th id="sortByTotalGoalDifferenceAway">DR</th>
                                    </tr>
                                </thead>
                                <?php
                                echo "<tbody class='mb-5'>";
                                $sql = "SELECT *, 
                                            (vinte_casa * 3 + pari_casa + vinte_trasferta * 3 + pari_trasferta) AS punti,
                                            (fatti_casa - subiti_casa + fatti_trasferta - subiti_trasferta) AS differenza_reti,
                                            (fatti_casa + fatti_trasferta) AS gol_fatti
                                        FROM $tablestatistiche
                                        WHERE utente = ? AND nome = ? AND girone = ?
                                        ORDER BY punti DESC, differenza_reti DESC, gol_fatti DESC, squadra ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ssi", $user, $name, $i);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $rank = 1;
                                while ($row = $result->fetch_assoc()) {
                                    $squadra = $row['squadra'];

                                    $vinte_casa = $row['vinte_casa'];
                                    $pari_casa = $row['pari_casa'];
                                    $perse_casa = $row['perse_casa'];
                                    $fatti_casa = $row['fatti_casa'];
                                    $subiti_casa = $row['subiti_casa'];
                                    $giocate_casa = $vinte_casa + $pari_casa + $perse_casa;
                                    $punti_casa = $vinte_casa * 3 + $pari_casa;
                                    $differenza_casa = $fatti_casa - $subiti_casa;

                                    $vinte_trasferta = $row['vinte_trasferta'];
                                    $pari_trasferta = $row['pari_trasferta'];
                                    $perse_trasferta = $row['perse_trasferta'];
                                    $fatti_trasferta = $row['fatti_trasferta'];
                                    $subiti_trasferta = $row['subiti_trasferta'];
                                    $giocate_trasferta = $vinte_trasferta + $pari_trasferta + $perse_trasferta;
                                    $punti_trasferta = $vinte_trasferta * 3 + $pari_trasferta;
                                    $differenza_trasferta = $fatti_trasferta - $subiti_trasferta;

                                    $vinte = $vinte_casa + $vinte_trasferta;
                                    $pari = $pari_casa + $pari_trasferta;
                                    $perse = $perse_casa + $perse_trasferta;
                                    $fatti = $fatti_casa + $fatti_trasferta;
                                    $subiti = $subiti_casa + $subiti_trasferta;
                                    $giocate = $giocate_casa + $giocate_trasferta;
                                    $punti = $punti_casa + $punti_trasferta;
                                    $differenza = $differenza_casa + $differenza_trasferta;

                                    $girone = $row['girone'];

                                    echo "<tr>";
                                    echo "<td>{$rank}</td>";
                                    echo "<td>{$squadra}</td>";
                                    echo "<td>{$punti}</td>";
                                    echo "<td>{$giocate}</td>";
                                    echo "<td>{$vinte}</td>";
                                    echo "<td>{$pari}</td>";
                                    echo "<td>{$perse}</td>";
                                    echo "<td>{$fatti}</td>";
                                    echo "<td>{$subiti}</td>";
                                    echo "<td>{$differenza}</td>";
                                    echo "<td>{$punti_casa}</td>";
                                    echo "<td>{$giocate_casa}</td>";
                                    echo "<td>{$vinte_casa}</td>";
                                    echo "<td>{$pari_casa}</td>";
                                    echo "<td>{$perse_casa}</td>";
                                    echo "<td>{$fatti_casa}</td>";
                                    echo "<td>{$subiti_casa}</td>";
                                    echo "<td>{$differenza_casa}</td>";
                                    echo "<td>{$punti_trasferta}</td>";
                                    echo "<td>{$giocate_trasferta}</td>";
                                    echo "<td>{$vinte_trasferta}</td>";
                                    echo "<td>{$pari_trasferta}</td>";
                                    echo "<td>{$perse_trasferta}</td>";
                                    echo "<td>{$fatti_trasferta}</td>";
                                    echo "<td>{$subiti_trasferta}</td>";
                                    echo "<td>{$differenza_trasferta}</td>";
                                    echo "</tr>";
                                    $rank++;
                                }
                                echo "</tbody></table>";
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="card-footer ">
                <?php include ("layout/menu_dettagli.php") ?>
                <?php if ($mod == "campionato" || $mod == "champions") { ?>
                    <div class="d-block d-lg-flex justify-content-evenly">
                        <a href="index.php?page=classifica&type=general&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                            class="btn btn-info my-2">Classifica Generale</a>
                        <a href="index.php?page=classifica&type=become&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                            class="btn btn-info my-2">Classifica Divenire</a>
                        <a href="index.php?page=classifica&type=home&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                            class="btn btn-info my-2">Classifica Casa</a>
                        <a href="index.php?page=classifica&type=away&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                            class="btn btn-info my-2">Classifica Trasferta</a>
                        <a href="index.php?page=classifica&type=firstround&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                            class="btn btn-info my-2">Classifica Andata</a>
                        <a href="index.php?page=classifica&type=returnround&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                            class="btn btn-info my-2">Classifica Ritorno</a>
                        <?php if ($mod == "champions") { ?>
                            <a href="index.php?page=classifica&type=rounds&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
                                class="btn btn-info my-2">Classifica Gironi</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>