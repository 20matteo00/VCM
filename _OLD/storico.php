<?php
$user = $_SESSION['username'];

if (isset($_GET['squadra'])) {
    $squadra = $_GET['squadra'];
    $query = "SELECT * FROM competizioni WHERE utente = ? order by nome";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    echo "<div class='container p-5 text-center'>";
    echo "<h2 class='mb-5' >Storico $squadra</h2>";
    echo "<div class='row'>";
    while ($row = $result->fetch_assoc()) {
        $nome = $row['nome'];
        $ar = $row['ar'];
        $modalita = $row['modalita'];
        $squadre = explode(",", $row['squadre']);

        if (in_array($squadra, $squadre)) {
            echo "<div class='col-md-12'>";
            echo "<div class='card mb-4 '>";
            echo "<div class='card-header'>";
            echo "<h4 class='m-0'>$nome</h4>";
            echo "</div>";
            echo "<div class='card-body'>";

            // Sanitize user and name
            $user_sanitized = sanitize_string($user);
            $name_sanitized = sanitize_string($nome);
            // Nome della tabella
            $tablepartite = "{$user_sanitized}_{$name_sanitized}_partite";

            // Query per ottenere le partite
            $query_partite = "SELECT * FROM $tablepartite WHERE squadra1 = ? OR squadra2 = ? order by giornata";
            $stmt_partite = $conn->prepare($query_partite);
            $stmt_partite->bind_param("ss", $squadra, $squadra);
            $stmt_partite->execute();
            $result_partite = $stmt_partite->get_result();
            $stmt_partite->close();

            if ($result_partite->num_rows > 0) {
                echo '<table class="table table-bordered">';
                echo '<thead><tr><th>Giornata</th><th>Partita</th><th>Risultato</th></tr></thead>';
                echo '<tbody>';
                while ($row_partite = $result_partite->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row_partite['giornata']) . '</td>';
                    echo '<td>' . htmlspecialchars($row_partite['squadra1']) . ' - ' . htmlspecialchars($row_partite['squadra2']) .  '</td>';
                    echo '<td>' . htmlspecialchars($row_partite['gol1']) . ' - ' . htmlspecialchars($row_partite['gol2']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p>No matches found for this team in this competition.</p>';
            }

            echo "</div>";
            echo "<div class='card-footer'>";
            if ($ar == 0) {
                echo "<p class='m-0'>" . $modalita . " - Solo Andata</p>";
            } elseif ($ar == 1) {
                echo "<p class='m-0'>" . $modalita . " - Andata e Ritorno</p>";
            }
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }
    echo "</div>";
    echo "</div>";
}

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
