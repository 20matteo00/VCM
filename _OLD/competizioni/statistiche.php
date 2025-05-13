<?php

$user = $_SESSION['username'];
$name = $_GET['name'];
$mod = $_GET['mod'];
$tablepartite = $_GET['tabpar'];
$tablestatistiche = $_GET['tabstat'];
if (isset($_GET['type']))
    $type = $_GET['type'];

$sql = "SELECT squadra FROM $tablestatistiche WHERE utente = ? AND nome = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $user, $name);
$stmt->execute();
$result = $stmt->get_result();

$squadre = [];
while ($row = $result->fetch_assoc()) {
    $squadre[] = $row['squadra'];
}
$stmt->close();
?>

<div class="container my-5">
    <?php include ("layout/menu_dettagli.php") ?>
    <div class="d-block d-lg-flex justify-content-evenly mb-5">
        <a href="index.php?page=statistiche&type=list&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
            class="btn btn-info my-2">Elenco Partite</a>
        <a href="index.php?page=statistiche&type=general&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
            class="btn btn-info my-2">Statistiche Generali</a>
        <a href="index.php?page=statistiche&type=individual&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>"
            class="btn btn-info my-2">Statistiche Individuali</a>
    </div>

    <?php if (isset($_GET['type']) && $type == 'list') { ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php
                    if (isset($_GET['team'])) {
                        $team = $_GET['team'];
                        $sql = "SELECT * FROM $tablepartite WHERE utente = ? AND nome = ? AND (squadra1 = ? OR squadra2 = ?) ORDER BY giornata";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ssss', $user, $name, $team, $team);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            echo '<div class="table table-responsive"> <table class="table table-striped text-center">';
                            echo '<thead><tr><th>Giornata</th><th>Partita</th><th>Risultato</th></tr></thead>';
                            echo '<tbody>';
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['giornata']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['squadra1']) . ' - ' . htmlspecialchars($row['squadra2']) . '</td>';
                                if(is_numeric($row['gol1']) && is_numeric($row['gol2'])){
                                    echo '<td>' . htmlspecialchars($row['gol1']) . ' - ' . htmlspecialchars($row['gol2']) . '</td>';
                                } else {
                                    echo '<td>-</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table></div>';
                        } else {
                            echo "Nessuna partita storica trovata per " . htmlspecialchars($team) . ".";
                        }
                        $stmt->close();
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="row text-center fw-bold mt-5">
            <?php foreach ($squadre as $squadra) { ?>
                <div class="col-3 py-2">
                    <a
                        href="?page=statistiche&type=list&name=<?php echo htmlspecialchars($name); ?>&mod=<?php echo htmlspecialchars($mod); ?>&tabpar=<?php echo htmlspecialchars($tablepartite); ?>&tabstat=<?php echo htmlspecialchars($tablestatistiche); ?>&team=<?php echo htmlspecialchars($squadra); ?>">
                        <div class="card">
                            <div class="card-body">
                                <?php echo htmlspecialchars($squadra); ?>

                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    <?php } elseif (isset($_GET['type']) && $type == 'general') { ?>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php
                    $sql = "SELECT squadre FROM competizioni WHERE utente = ? AND nome = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ss', $user, $name);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $teams = [];
                    if ($row = $result->fetch_assoc()) {
                        $teams = explode(",", $row['squadre']);
                    }
                    $stmt->close();
                    $teams = array_unique($teams);

                    $maxSerieW = [];
                    $maxSerieL = [];
                    $maxSerieD = [];
                    $maxSerieWH = []; // Vittorie in casa
                    $maxSerieLH = []; // Sconfitte in casa
                    $maxSerieDH = []; // Pareggi in casa
                    $maxSerieWA = []; // Vittorie in trasferta
                    $maxSerieLA = []; // Sconfitte in trasferta
                    $maxSerieDA = []; // Pareggi in trasferta
                
                    $giornataBW = [];
                    $giornataBL = [];
                    $giornataBD = [];
                    $giornataEW = [];
                    $giornataEL = [];
                    $giornataED = [];
                    $giornataBWH = []; // Inizio vittorie in casa
                    $giornataBLH = []; // Inizio sconfitte in casa
                    $giornataBDH = []; // Inizio pareggi in casa
                    $giornataEWH = []; // Fine vittorie in casa
                    $giornataELH = []; // Fine sconfitte in casa
                    $giornataEDH = []; // Fine pareggi in casa
                    $giornataBWA = []; // Inizio vittorie in trasferta
                    $giornataBLA = []; // Inizio sconfitte in trasferta
                    $giornataBDA = []; // Inizio pareggi in trasferta
                    $giornataEWA = []; // Fine vittorie in trasferta
                    $giornataELA = []; // Fine sconfitte in trasferta
                    $giornataEDA = []; // Fine pareggi in trasferta
                
                    foreach ($teams as $team) {
                        // Initialize series counters
                        $serieW = $serieL = $serieD = 0;
                        $serieWH = $serieLH = $serieDH = 0;
                        $serieWA = $serieLA = $serieDA = 0;

                        $maxSerieW[$team] = $maxSerieL[$team] = $maxSerieD[$team] = 0;
                        $maxSerieWH[$team] = $maxSerieLH[$team] = $maxSerieDH[$team] = 0;
                        $maxSerieWA[$team] = $maxSerieLA[$team] = $maxSerieDA[$team] = 0;

                        $sql = "SELECT * FROM $tablepartite WHERE utente = ? AND nome = ? AND (squadra1 = ? OR squadra2 = ?) ORDER BY giornata";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ssss', $user, $name, $team, $team);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            if ($row['squadra1'] == $team) {
                                // Partite in casa
                                if ($row['gol1'] > $row['gol2']) {
                                    $serieW++;
                                    $serieWH++;
                                    $serieL = 0;
                                    $serieLH = 0;
                                    $serieD = 0;
                                    $serieDH = 0;
                                    if ($serieW == 1) {
                                        $startGiornataW = $row['giornata'];
                                    }
                                    if ($serieWH == 1) {
                                        $startGiornataWH = $row['giornata'];
                                    }
                                    if ($serieW > $maxSerieW[$team]) {
                                        $maxSerieW[$team] = $serieW;
                                        $giornataBW[$team] = $startGiornataW;
                                        $giornataEW[$team] = $row['giornata'];
                                    }
                                    if ($serieWH > $maxSerieWH[$team]) {
                                        $maxSerieWH[$team] = $serieWH;
                                        $giornataBWH[$team] = $startGiornataWH;
                                        $giornataEWH[$team] = $row['giornata'];
                                    }
                                } elseif ($row['gol1'] < $row['gol2']) {
                                    $serieW = 0;
                                    $serieWH = 0;
                                    $serieL++;
                                    $serieLH++;
                                    $serieD = 0;
                                    $serieDH = 0;
                                    if ($serieL == 1) {
                                        $startGiornataL = $row['giornata'];
                                    }
                                    if ($serieLH == 1) {
                                        $startGiornataLH = $row['giornata'];
                                    }
                                    if ($serieL > $maxSerieL[$team]) {
                                        $maxSerieL[$team] = $serieL;
                                        $giornataBL[$team] = $startGiornataL;
                                        $giornataEL[$team] = $row['giornata'];
                                    }
                                    if ($serieLH > $maxSerieLH[$team]) {
                                        $maxSerieLH[$team] = $serieLH;
                                        $giornataBLH[$team] = $startGiornataLH;
                                        $giornataELH[$team] = $row['giornata'];
                                    }
                                } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                    $serieW = 0;
                                    $serieWH = 0;
                                    $serieL = 0;
                                    $serieLH = 0;
                                    $serieD++;
                                    $serieDH++;
                                    if ($serieD == 1) {
                                        $startGiornataD = $row['giornata'];
                                    }
                                    if ($serieDH == 1) {
                                        $startGiornataDH = $row['giornata'];
                                    }
                                    if ($serieD > $maxSerieD[$team]) {
                                        $maxSerieD[$team] = $serieD;
                                        $giornataBD[$team] = $startGiornataD;
                                        $giornataED[$team] = $row['giornata'];
                                    }
                                    if ($serieDH > $maxSerieDH[$team]) {
                                        $maxSerieDH[$team] = $serieDH;
                                        $giornataBDH[$team] = $startGiornataDH;
                                        $giornataEDH[$team] = $row['giornata'];
                                    }
                                }
                            } elseif ($row['squadra2'] == $team) {
                                // Partite in trasferta
                                if ($row['gol1'] < $row['gol2']) {
                                    $serieW++;
                                    $serieWA++;
                                    $serieL = 0;
                                    $serieLA = 0;
                                    $serieD = 0;
                                    $serieDA = 0;
                                    if ($serieW == 1) {
                                        $startGiornataW = $row['giornata'];
                                    }
                                    if ($serieWA == 1) {
                                        $startGiornataWA = $row['giornata'];
                                    }
                                    if ($serieW > $maxSerieW[$team]) {
                                        $maxSerieW[$team] = $serieW;
                                        $giornataBW[$team] = $startGiornataW;
                                        $giornataEW[$team] = $row['giornata'];
                                    }
                                    if ($serieWA > $maxSerieWA[$team]) {
                                        $maxSerieWA[$team] = $serieWA;
                                        $giornataBWA[$team] = $startGiornataWA;
                                        $giornataEWA[$team] = $row['giornata'];
                                    }
                                } elseif ($row['gol1'] > $row['gol2']) {
                                    $serieW = 0;
                                    $serieWA = 0;
                                    $serieL++;
                                    $serieLA++;
                                    $serieD = 0;
                                    $serieDA = 0;
                                    if ($serieL == 1) {
                                        $startGiornataL = $row['giornata'];
                                    }
                                    if ($serieLA == 1) {
                                        $startGiornataLA = $row['giornata'];
                                    }
                                    if ($serieL > $maxSerieL[$team]) {
                                        $maxSerieL[$team] = $serieL;
                                        $giornataBL[$team] = $startGiornataL;
                                        $giornataEL[$team] = $row['giornata'];
                                    }
                                    if ($serieLA > $maxSerieLA[$team]) {
                                        $maxSerieLA[$team] = $serieLA;
                                        $giornataBLA[$team] = $startGiornataLA;
                                        $giornataELA[$team] = $row['giornata'];
                                    }
                                } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) {
                                    $serieW = 0;
                                    $serieWA = 0;
                                    $serieL = 0;
                                    $serieLA = 0;
                                    $serieD++;
                                    $serieDA++;
                                    if ($serieD == 1) {
                                        $startGiornataD = $row['giornata'];
                                    }
                                    if ($serieDA == 1) {
                                        $startGiornataDA = $row['giornata'];
                                    }
                                    if ($serieD > $maxSerieD[$team]) {
                                        $maxSerieD[$team] = $serieD;
                                        $giornataBD[$team] = $startGiornataD;
                                        $giornataED[$team] = $row['giornata'];
                                    }
                                    if ($serieDA > $maxSerieDA[$team]) {
                                        $maxSerieDA[$team] = $serieDA;
                                        $giornataBDA[$team] = $startGiornataDA;
                                        $giornataEDA[$team] = $row['giornata'];
                                    }
                                }
                            }
                        }

                        $stmt->close();
                    }

                    // Trova tutte le squadre con la massima serie per ciascuna categoria
                    $maxWinValue = max($maxSerieW);
                    $maxLoseValue = max($maxSerieL);
                    $maxDrawValue = max($maxSerieD);
                    $maxWinHValue = max($maxSerieWH);
                    $maxLoseHValue = max($maxSerieLH);
                    $maxDrawHValue = max($maxSerieDH);
                    $maxWinAValue = max($maxSerieWA);
                    $maxLoseAValue = max($maxSerieLA);
                    $maxDrawAValue = max($maxSerieDA);

                    $maxWinTeams = array_keys($maxSerieW, $maxWinValue);
                    $maxLoseTeams = array_keys($maxSerieL, $maxLoseValue);
                    $maxDrawTeams = array_keys($maxSerieD, $maxDrawValue);
                    $maxWinTeamsH = array_keys($maxSerieWH, $maxWinHValue);
                    $maxLoseTeamsH = array_keys($maxSerieLH, $maxLoseHValue);
                    $maxDrawTeamsH = array_keys($maxSerieDH, $maxDrawHValue);
                    $maxWinTeamsA = array_keys($maxSerieWA, $maxWinAValue);
                    $maxLoseTeamsA = array_keys($maxSerieLA, $maxLoseAValue);
                    $maxDrawTeamsA = array_keys($maxSerieDA, $maxDrawAValue);


                    $sql = "SELECT * FROM $tablepartite WHERE utente = ? AND nome = ? ORDER BY giornata";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ss', $user, $name);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $maxgol = 0;
                    $gapgol = 0;
                    $infomaxgol = []; // Array per le partite con il punteggio massimo
                    $infogapgol = []; // Array per le partite con il gap massimo
                
                    while ($row = $result->fetch_assoc()) {
                        $totalGoals = $row['gol1'] + $row['gol2'];
                        if ($totalGoals > $maxgol && $totalGoals != 0) {
                            $maxgol = $totalGoals;
                            $infomaxgol = [ // Inizializza con la nuova partita massima
                                [
                                    'squadra1' => $row['squadra1'],
                                    'squadra2' => $row['squadra2'],
                                    'gol1' => $row['gol1'],
                                    'gol2' => $row['gol2'],
                                    'giornata' => $row['giornata']
                                ]
                            ];
                        } elseif ($totalGoals == $maxgol && $totalGoals != 0) {
                            $infomaxgol[] = [ // Aggiungi la partita a parimerito
                                'squadra1' => $row['squadra1'],
                                'squadra2' => $row['squadra2'],
                                'gol1' => $row['gol1'],
                                'gol2' => $row['gol2'],
                                'giornata' => $row['giornata']
                            ];
                        }
                        $gapGoals = abs($row['gol1'] - $row['gol2']);
                        if ($gapGoals > $gapgol && $gapGoals != 0) {
                            $gapgol = $gapGoals;
                            $infogapgol = [ // Inizializza con la nuova partita massima
                                [
                                    'squadra1' => $row['squadra1'],
                                    'squadra2' => $row['squadra2'],
                                    'gol1' => $row['gol1'],
                                    'gol2' => $row['gol2'],
                                    'giornata' => $row['giornata']
                                ]
                            ];
                        } elseif ($gapGoals == $gapgol && $gapGoals != 0) {
                            $infogapgol[] = [ // Aggiungi la partita a parimerito
                                'squadra1' => $row['squadra1'],
                                'squadra2' => $row['squadra2'],
                                'gol1' => $row['gol1'],
                                'gol2' => $row['gol2'],
                                'giornata' => $row['giornata']
                            ];
                        }
                    }

                    $stmt->close();

                    // Stampa i risultati
                    ?>
                    <div class="table table-responsive">
                        <table class="table table-striped text-center">
                            <thead>
                                <tr>
                                    <th>Record</th>
                                    <th>Squadra</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Maggior Numero di Vittorie Consecutive:</td>
                                    <td>
                                        <?php
                                        foreach ($maxWinTeams as $team) {
                                            if (isset($maxSerieW[$team]) && isset($giornataBW[$team]) && isset($giornataEW[$team])) {
                                                echo $maxSerieW[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBW[$team] . "° - " . $giornataEW[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Pareggi Consecutivi:</td>
                                    <td>
                                        <?php
                                        foreach ($maxDrawTeams as $team) {
                                            if (isset($maxSerieD[$team]) && isset($giornataBD[$team]) && isset($giornataED[$team])) {
                                                echo $maxSerieD[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBD[$team] . "° - " . $giornataED[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Sconfitte Consecutive:</td>
                                    <td>
                                        <?php
                                        foreach ($maxLoseTeams as $team) {
                                            if (isset($maxSerieL[$team]) && isset($giornataBL[$team]) && isset($giornataEL[$team])) {
                                                echo $maxSerieL[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBL[$team] . "° - " . $giornataEL[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Vittorie Consecutive in Casa:</td>
                                    <td>
                                        <?php
                                        foreach ($maxWinTeamsH as $team) {
                                            if (isset($maxSerieWH[$team]) && isset($giornataBWH[$team]) && isset($giornataEWH[$team])) {
                                                echo $maxSerieWH[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBWH[$team] . "° - " . $giornataEWH[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Pareggi Consecutivi in Casa:</td>
                                    <td>
                                        <?php
                                        foreach ($maxDrawTeamsH as $team) {
                                            if (isset($maxSerieDH[$team]) && isset($giornataBDH[$team]) && isset($giornataEDH[$team])) {
                                                echo $maxSerieDH[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBDH[$team] . "° - " . $giornataEDH[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Sconfitte Consecutive in Casa:</td>
                                    <td>
                                        <?php
                                        foreach ($maxLoseTeamsH as $team) {
                                            if (isset($maxSerieLH[$team]) && isset($giornataBLH[$team]) && isset($giornataELH[$team])) {
                                                echo $maxSerieLH[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBLH[$team] . "° - " . $giornataELH[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Vittorie Consecutive in Trasferta:</td>
                                    <td>
                                        <?php
                                        foreach ($maxWinTeamsA as $team) {
                                            if (isset($maxSerieWA[$team]) && isset($giornataBWA[$team]) && isset($giornataEWA[$team])) {
                                                echo $maxSerieWA[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBWA[$team] . "° - " . $giornataEWA[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Pareggi Consecutivi in Trasferta:</td>
                                    <td>
                                        <?php
                                        foreach ($maxDrawTeamsA as $team) {
                                            if (isset($maxSerieDA[$team]) && isset($giornataBDA[$team]) && isset($giornataEDA[$team])) {
                                                echo $maxSerieDA[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBDA[$team] . "° - " . $giornataEDA[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maggior Numero di Sconfitte Consecutive in Trasferta:</td>
                                    <td>
                                        <?php
                                        foreach ($maxLoseTeamsA as $team) {
                                            if (isset($maxSerieLA[$team]) && isset($giornataBLA[$team]) && isset($giornataELA[$team])) {
                                                echo $maxSerieLA[$team] . ", " . htmlspecialchars($team) . " (" . $giornataBLA[$team] . "° - " . $giornataELA[$team] . "°)<br>";
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Partita con più Gol:</td>
                                    <td>
                                        <?php
                                        foreach ($infomaxgol as $partita) {
                                            echo htmlspecialchars($maxgol) . ", " . htmlspecialchars($partita['squadra1']) . " " . htmlspecialchars($partita['squadra2']) . " " . htmlspecialchars($partita['gol1']) . " - " . htmlspecialchars($partita['gol2']) . " (" . htmlspecialchars($partita['giornata']) . "°)<br>";
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Partita con più Scarto di Gol:</td>
                                    <td>
                                        <?php
                                        foreach ($infogapgol as $partita) {
                                            echo htmlspecialchars($gapgol) . ", " . htmlspecialchars($partita['squadra1']) . " " . htmlspecialchars($partita['squadra2']) . " " . htmlspecialchars($partita['gol1']) . " - " . htmlspecialchars($partita['gol2']) . " (" . htmlspecialchars($partita['giornata']) . "°)<br>";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                </div>
            </div>
        </div>
    <?php } elseif (isset($_GET['type']) && $type == 'individual') { ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php

                    if (isset($_GET['teams'])) {
                        $team = $_GET['teams'];
                        $sql = "SELECT * FROM $tablepartite WHERE utente = ? AND nome = ? AND (squadra1 = ? OR squadra2 = ?) ORDER BY giornata";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ssss', $user, $name, $team, $team);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Inizializzazione delle variabili
                        $serieW = $serieL = $serieD = 0;
                        $serieWH = $serieLH = $serieDH = 0;
                        $serieWA = $serieLA = $serieDA = 0;

                        $maxSerieW = $maxSerieL = $maxSerieD = 0;
                        $maxSerieWH = $maxSerieLH = $maxSerieDH = 0;
                        $maxSerieWA = $maxSerieLA = $maxSerieDA = 0;

                        $giornataBW = $giornataEW = $giornataBL = $giornataEL = $giornataBD = $giornataED = 0;
                        $giornataBWH = $giornataEWH = $giornataBLH = $giornataELH = $giornataBDH = $giornataEDH = 0;
                        $giornataBWA = $giornataEWA = $giornataBLA = $giornataELA = $giornataBDA = $giornataEDA = 0;


                        $maxwin = 0;
                        $maxlose = 0;
                        $infomaxwin = []; // Array per le partite con il punteggio massimo
                        $infomaxlose = []; // Array per le partite con il gap massimo
                        // Elaborazione dei risultati
                        while ($row = $result->fetch_assoc()) {
                            $giornata = $row['giornata'];

                            // Controllo per la squadra in casa
                            if ($row['squadra1'] === $team) {
                                if ($row['gol1'] > $row['gol2']) { // Vittoria
                                    $serieW++;
                                    $serieWH++;
                                    $serieL = 0;
                                    $serieLH = 0;
                                    $serieD = 0;
                                    $serieDH = 0;

                                    $win = $row['gol1'] - $row['gol2'];
                                    if ($win > $maxwin) {
                                        $maxwin = $win;
                                        $infomaxwin = [ // Inizializza con la nuova partita massima
                                            [
                                                'squadra1' => $row['squadra1'],
                                                'squadra2' => $row['squadra2'],
                                                'gol1' => $row['gol1'],
                                                'gol2' => $row['gol2'],
                                                'giornata' => $row['giornata']
                                            ]
                                        ];
                                    } elseif ($win == $maxwin) {
                                        $infomaxwin[] = [ // Aggiungi la partita a parimerito
                                            'squadra1' => $row['squadra1'],
                                            'squadra2' => $row['squadra2'],
                                            'gol1' => $row['gol1'],
                                            'gol2' => $row['gol2'],
                                            'giornata' => $row['giornata']
                                        ];
                                    }


                                    if ($serieW == 1) {
                                        $startGiornataW = $giornata;
                                    }
                                    if ($serieWH == 1) {
                                        $startGiornataWH = $giornata;
                                    }
                                    if ($serieW > $maxSerieW) {
                                        $maxSerieW = $serieW;
                                        $giornataBW = $startGiornataW;
                                        $giornataEW = $giornata;
                                    }
                                    if ($serieWH > $maxSerieWH) {
                                        $maxSerieWH = $serieWH;
                                        $giornataBWH = $startGiornataWH;
                                        $giornataEWH = $giornata;
                                    }
                                } elseif ($row['gol1'] < $row['gol2']) { // Sconfitta
                                    $serieWH = 0;
                                    $serieW = 0;
                                    $serieL++;
                                    $serieLH++;
                                    $serieD = 0;
                                    $serieDH = 0;

                                    $lose = $row['gol2'] - $row['gol1'];
                                    if ($lose > $maxlose) {
                                        $maxlose = $lose;
                                        $infomaxlose = [ // Inizializza con la nuova partita massima
                                            [
                                                'squadra1' => $row['squadra1'],
                                                'squadra2' => $row['squadra2'],
                                                'gol1' => $row['gol1'],
                                                'gol2' => $row['gol2'],
                                                'giornata' => $row['giornata']
                                            ]
                                        ];
                                    } elseif ($lose == $maxlose) {
                                        $infomaxlose[] = [ // Aggiungi la partita a parimerito
                                            'squadra1' => $row['squadra1'],
                                            'squadra2' => $row['squadra2'],
                                            'gol1' => $row['gol1'],
                                            'gol2' => $row['gol2'],
                                            'giornata' => $row['giornata']
                                        ];
                                    }

                                    if ($serieL == 1) {
                                        $startGiornataL = $giornata;
                                    }
                                    if ($serieLH == 1) {
                                        $startGiornataLH = $giornata;
                                    }
                                    if ($serieL > $maxSerieL) {
                                        $maxSerieL = $serieL;
                                        $giornataBL = $startGiornataL;
                                        $giornataEL = $giornata;
                                    }
                                    if ($serieLH > $maxSerieLH) {
                                        $maxSerieLH = $serieLH;
                                        $giornataBLH = $startGiornataLH;
                                        $giornataELH = $giornata;
                                    }
                                } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) { // Pareggio
                                    $serieWH = 0;
                                    $serieW = 0;
                                    $serieL = 0;
                                    $serieLH = 0;
                                    $serieD++;
                                    $serieDH++;

                                    if ($serieD == 1) {
                                        $startGiornataD = $giornata;
                                    }
                                    if ($serieDH == 1) {
                                        $startGiornataDH = $giornata;
                                    }
                                    if ($serieD > $maxSerieD) {
                                        $maxSerieD = $serieD;
                                        $giornataBD = $startGiornataD;
                                        $giornataED = $giornata;
                                    }
                                    if ($serieDH > $maxSerieDH) {
                                        $maxSerieDH = $serieDH;
                                        $giornataBDH = $startGiornataDH;
                                        $giornataEDH = $giornata;
                                    }
                                }
                            } else {
                                // Controllo per la squadra in trasferta
                                if ($row['gol2'] > $row['gol1']) { // Vittoria
                                    $serieW++;
                                    $serieWA++;
                                    $serieL = 0;
                                    $serieLA = 0;
                                    $serieD = 0;
                                    $serieDA = 0;

                                    $win = $row['gol2'] - $row['gol1'];
                                    if ($win > $maxwin) {
                                        $maxwin = $win;
                                        $infomaxwin = [ // Inizializza con la nuova partita massima
                                            [
                                                'squadra1' => $row['squadra1'],
                                                'squadra2' => $row['squadra2'],
                                                'gol1' => $row['gol1'],
                                                'gol2' => $row['gol2'],
                                                'giornata' => $row['giornata']
                                            ]
                                        ];
                                    } elseif ($win == $maxwin) {
                                        $infomaxwin[] = [ // Aggiungi la partita a parimerito
                                            'squadra1' => $row['squadra1'],
                                            'squadra2' => $row['squadra2'],
                                            'gol1' => $row['gol1'],
                                            'gol2' => $row['gol2'],
                                            'giornata' => $row['giornata']
                                        ];
                                    }

                                    if ($serieW == 1) {
                                        $startGiornataW = $giornata;
                                    }
                                    if ($serieWA == 1) {
                                        $startGiornataWA = $giornata;
                                    }
                                    if ($serieW > $maxSerieW) {
                                        $maxSerieW = $serieW;
                                        $giornataBW = $startGiornataW;
                                        $giornataEW = $giornata;
                                    }
                                    if ($serieWA > $maxSerieWA) {
                                        $maxSerieWA = $serieWA;
                                        $giornataBWA = $startGiornataWA;
                                        $giornataEWA = $giornata;
                                    }
                                } elseif ($row['gol2'] < $row['gol1']) { // Sconfitta
                                    $serieWA = 0;
                                    $serieW = 0;
                                    $serieL++;
                                    $serieLA++;
                                    $serieD = 0;
                                    $serieDA = 0;

                                    $lose = $row['gol1'] - $row['gol2'];
                                    if ($lose > $maxlose) {
                                        $maxlose = $lose;
                                        $infomaxlose = [ // Inizializza con la nuova partita massima
                                            [
                                                'squadra1' => $row['squadra1'],
                                                'squadra2' => $row['squadra2'],
                                                'gol1' => $row['gol1'],
                                                'gol2' => $row['gol2'],
                                                'giornata' => $row['giornata']
                                            ]
                                        ];
                                    } elseif ($lose == $maxlose) {
                                        $infomaxlose[] = [ // Aggiungi la partita a parimerito
                                            'squadra1' => $row['squadra1'],
                                            'squadra2' => $row['squadra2'],
                                            'gol1' => $row['gol1'],
                                            'gol2' => $row['gol2'],
                                            'giornata' => $row['giornata']
                                        ];
                                    }

                                    if ($serieL == 1) {
                                        $startGiornataL = $giornata;
                                    }
                                    if ($serieLA == 1) {
                                        $startGiornataLA = $giornata;
                                    }
                                    if ($serieL > $maxSerieL) {
                                        $maxSerieL = $serieL;
                                        $giornataBL = $startGiornataL;
                                        $giornataEL = $giornata;
                                    }
                                    if ($serieLA > $maxSerieLA) {
                                        $maxSerieLA = $serieLA;
                                        $giornataBLA = $startGiornataLA;
                                        $giornataELA = $giornata;
                                    }
                                } elseif ($row['gol1'] == $row['gol2'] && is_numeric($row['gol1']) && is_numeric($row['gol2'])) { // Pareggio
                                    $serieWA = 0;
                                    $serieW = 0;
                                    $serieL = 0;
                                    $serieLA = 0;
                                    $serieD++;
                                    $serieDA++;

                                    if ($serieD == 1) {
                                        $startGiornataD = $giornata;
                                    }
                                    if ($serieDA == 1) {
                                        $startGiornataDA = $giornata;
                                    }
                                    if ($serieD > $maxSerieD) {
                                        $maxSerieD = $serieD;
                                        $giornataBD = $startGiornataD;
                                        $giornataED = $giornata;
                                    }
                                    if ($serieDA > $maxSerieDA) {
                                        $maxSerieDA = $serieDA;
                                        $giornataBDA = $startGiornataDA;
                                        $giornataEDA = $giornata;
                                    }
                                }
                            }



                        }

                        // Chiudere il prepared statement
                        $stmt->close();


                        ?>
                        <div class="table table-responsive">
                            <table class="table table-striped text-center">
                                <thead>
                                    <tr>
                                        <th>Statistiche per <?php echo htmlspecialchars($team); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Maggior Numero di Vittorie Consecutive: </td>
                                        <td><?php echo $maxSerieW; ?>
                                            (<?php echo $giornataBW; ?>° - <?php echo $giornataEW; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Pareggi Consecutivi: </td>
                                        <td><?php echo $maxSerieD; ?>
                                            (<?php echo $giornataBD; ?>° - <?php echo $giornataED; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Sconfitte Consecutive: </td>
                                        <td><?php echo $maxSerieL; ?>
                                            (<?php echo $giornataBL; ?>° - <?php echo $giornataEL; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Vittorie Consecutive in Casa: </td>
                                        <td><?php echo $maxSerieWH; ?>
                                            (<?php echo $giornataBWH; ?>° - <?php echo $giornataEWH; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Pareggi Consecutivi in Casa:</td>
                                        <td> <?php echo $maxSerieDH; ?>
                                            (<?php echo $giornataBDH; ?>° - <?php echo $giornataEDH; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Sconfitte Consecutive in Casa:</td>
                                        <td> <?php echo $maxSerieLH; ?>
                                            (<?php echo $giornataBLH; ?>° - <?php echo $giornataELH; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Vittorie Consecutive in Trasferta: </td>
                                        <td><?php echo $maxSerieWA; ?>
                                            (<?php echo $giornataBWA; ?>° - <?php echo $giornataEWA; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Pareggi Consecutivi in Trasferta: </td>
                                        <td><?php echo $maxSerieDA; ?>
                                            (<?php echo $giornataBDA; ?>° - <?php echo $giornataEDA; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Maggior Numero di Sconfitte Consecutive in Trasferta:</td>
                                        <td><?php echo $maxSerieLA; ?>
                                            (<?php echo $giornataBLA; ?>° - <?php echo $giornataELA; ?>°)</td>
                                    </tr>
                                    <tr>
                                        <td>Miglior Vittoria:</td>
                                        <td>
                                            <?php
                                            foreach ($infomaxwin as $partita) {
                                                echo htmlspecialchars($maxwin) . ", " . htmlspecialchars($partita['squadra1']) . " " . htmlspecialchars($partita['squadra2']) . " " . htmlspecialchars($partita['gol1']) . " - " . htmlspecialchars($partita['gol2']) . " (" . htmlspecialchars($partita['giornata']) . "°)<br>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Peggior Sconfitta:</td>
                                        <td>
                                            <?php
                                            foreach ($infomaxlose as $partita) {
                                                echo htmlspecialchars($maxlose) . ", " . htmlspecialchars($partita['squadra1']) . " " . htmlspecialchars($partita['squadra2']) . " " . htmlspecialchars($partita['gol1']) . " - " . htmlspecialchars($partita['gol2']) . " (" . htmlspecialchars($partita['giornata']) . "°)<br>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php }
                    ?>

                </div>
            </div>
        </div>
        <div class="row text-center fw-bold mt-5">
            <?php foreach ($squadre as $squadra) { ?>
                <div class="col-3 py-2">
                    <a
                        href="?page=statistiche&type=individual&name=<?php echo htmlspecialchars($name); ?>&mod=<?php echo htmlspecialchars($mod); ?>&tabpar=<?php echo htmlspecialchars($tablepartite); ?>&tabstat=<?php echo htmlspecialchars($tablestatistiche); ?>&teams=<?php echo htmlspecialchars($squadra); ?>">
                        <div class="card">
                            <div class="card-body">
                                <?php echo htmlspecialchars($squadra); ?>

                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>