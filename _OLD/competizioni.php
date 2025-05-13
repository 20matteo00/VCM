<?php
if (isset($_GET['com'])) {
    $finita = $_GET['com'];
    $user = $_SESSION['username'];

    
    // Prepara la query SQL per selezionare le competizioni in base allo stato (finita o non finita)
    $sql_select = "SELECT * FROM competizioni WHERE finita = ? AND utente = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("is", $finita,$user);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    echo "<div class='container mt-5 text-center'>";
    // Controlla se ci sono risultati
    if ($result->num_rows > 0) {
        
        echo "<table class='table'>";
        echo "<thead><tr><th>".$VCM_name."</th><th>".$VCM_modality."</th><th>".$VCM_round_trip."</th><th>".$VCM_partecipants."</th><th>".$VCM_actions."</th></tr></thead>";
        echo "<tbody class='align-middle'>";
        
        // Mostra i dati delle competizioni in una tabella
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
            if(htmlspecialchars($row['modalita']) == "campionato"){
                echo "<td>" . $VCM_league . "</td>";
            } elseif(htmlspecialchars($row['modalita']) == "eliminazione"){
                echo "<td>" . $VCM_elimination . "</td>";
            } elseif(htmlspecialchars($row['modalita']) == "champions"){
                echo "<td>" . $VCM_champions . "</td>";
            }
            
            echo "<td>" . ($row['ar'] ? "Sì" : "No") . "</td>";
            echo "<td>" . htmlspecialchars($row['partecipanti']) . "</td>";
            echo "<td>"?>
            <a href="index.php?page=azioni&name=visualizzacompetizione&competizione=<?php echo $row['nome']; ?>&com=<?php echo $finita; ?>&mod=<?php echo $row['modalita']; ?>"
                class="btn btn-success"><?php echo $VCM_view; ?></a>
            <a href="index.php?page=azioni&name=eliminacompetizione&competizione=<?php echo $row['nome']; ?>&com=<?php echo $finita; ?>&mod=<?php echo $row['modalita']; ?>"
                class="btn btn-danger"><?php echo $VCM_delete; ?></a>
            <?php echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<span class='bg-danger fw-bold p-2'>".$VCM_no_competition."</span>";
    }
    echo "</div>";

    // Chiusura dello statement preparato
    $stmt_select->close();
}

?>