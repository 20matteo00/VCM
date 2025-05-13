<?php
// Messaggi di successo o errore
$success_message = '';
$error_message = '';

// Verifica se il modulo è stato inviato per l'aggiunta di una nuova squadra
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Ricevi i dati dal modulo
    $user = $_SESSION['username'];
    $nome = ucwords(strtolower(trim($_POST['nome'])));;
    $forza = $_POST['forza'];
    $colore_primario = $_POST['colore_primario'];
    $colore_secondario = $_POST['colore_secondario'];
    $gruppo = $_POST['gruppo'];

    // Esegui una query per inserire la squadra nel database
    $query = "INSERT INTO squadre (utente, nome, forza, gruppo, colore1, colore2) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        $error_message = $VCM_error3 . $conn->error;
    } else {
        $stmt->bind_param("ssisss", $user, $nome, $forza, $gruppo, $colore_primario, $colore_secondario);
        try {
            if ($stmt->execute()) {
                // Squadra aggiunta con successo
                header("Location: index.php?page=giocatori");
            } else {
                throw new mysqli_sql_exception($stmt->error, $stmt->errno);
            }
        } catch (mysqli_sql_exception $e) {
            // Errore durante l'aggiunta della squadra
            $_SESSION['error_message'] = $VCM_insert_player_error;
            // Registra il messaggio di errore dettagliato nel log per l'amministratore
            error_log($e->getMessage());
        }
        $stmt->close();
    }

    // Reindirizza alla stessa pagina per evitare duplicati
    header("Location: index.php?page=giocatori");
    exit;
}

// Recupera le squadre dall'utente corrente
$user = $_SESSION['username'];
$query_squadre = "SELECT * FROM squadre WHERE utente = ? ORDER BY forza DESC";
$stmt_squadre = $conn->prepare($query_squadre);
if ($stmt_squadre === false) {
    $error_message = $VCM_error3 . $conn->error;
} else {
    $stmt_squadre->bind_param("s", $user);
    $stmt_squadre->execute();
    $result_squadre = $stmt_squadre->get_result();
    $stmt_squadre->close();
}
$i = 1;

// Recupera i gruppi dell'utente corrente per popolare il campo select
$query_gruppi = "SELECT nome FROM gruppi WHERE utente = ? ORDER BY nome asc";
$stmt_gruppi = $conn->prepare($query_gruppi);
$stmt_gruppi->bind_param("s", $user);
$stmt_gruppi->execute();
$result_gruppi = $stmt_gruppi->get_result();
$stmt_gruppi->close();
?>

<div class="container">
    <div class="row justify-content-center">
        <h2 class="text-center mt-5"><?php echo $VCM_manage_players; ?></h2>
        <!-- Messaggio di successo -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <div class="alert alert-success mt-3">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <!-- Messaggio di errore -->
        <?php if (isset($_SESSION['error_message'])) : ?>
            <div class="alert alert-danger mt-3">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>
        <form action="#" method="post" class="mt-4">
            <input type="hidden" id="form_submitted" name="form_submitted" value="0">
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_name; ?>" class="form-label"><?php echo $VCM_name; ?></label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_strength; ?>" class="form-label"><?php echo $VCM_strength; ?></label>
                        <input type="number" class="form-control" id="forza" name="forza" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="gruppo" class="form-label">Gruppo</label>
                        <select class="form-control" id="gruppo" name="gruppo" required>
                            <?php while ($row = $result_gruppi->fetch_assoc()) : ?>
                            <option value="<?php echo htmlspecialchars($row['nome']); ?>">
                                <?php echo htmlspecialchars($row['nome']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_color1; ?>" class="form-label"><?php echo $VCM_color1; ?></label>
                        <input type="color" class="form-control" id="colore_primario" name="colore_primario" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <label for="<?php echo $VCM_color2; ?>" class="form-label"><?php echo $VCM_color2; ?></label>
                        <input type="color" class="form-control" id="colore_secondario" name="colore_secondario" required>
                    </div>
                </div>
                <div class="col-lg-2 text-center">
                    <div class="mb-3">
                        <!-- Utilizza la classe mt-4 per aggiungere spazio sopra il bottone -->
                        <button type="submit" class="btn btn-success w-100 mt-4" name="submit"><?php echo $VCM_add; ?></button>
                    </div>
                </div>
            </div>
        </form>
        <!-- Elenco delle squadre -->
        <div class="row mt-5 justify-content-center">
            <div class="col-auto table-responsive">
                <h3 class="text-center"><?php echo $VCM_list_players; ?></h3>
                <table class="table table-bordered text-center table-responsive miatable" id="myTable">
                    <thead>
                        <tr>
                            <th class="mioth" id="sortById">#</th>
                            <th class="mioth" id="sortByName"><?php echo $VCM_player; ?></th>
                            <th class="mioth" id="sortByStrength"><?php echo $VCM_strength; ?></th>
                            <th class="mioth" id="sortByGroup">Gruppo</th>
                            <th><?php echo $VCM_actions; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($result_squadre)) : ?>
                            <?php while ($row = $result_squadre->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td>
                                        <div class="miocolore px-2" style="background-color: <?php echo htmlspecialchars($row['colore1']); ?>; color: <?php echo htmlspecialchars($row['colore2']); ?>;">
                                            <?php echo htmlspecialchars($row['nome']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['forza']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gruppo']); ?></td>
                                    <td>
                                        <a href="index.php?page=azioni&name=modificasquadra&squadra=<?php echo $row['nome']; ?>" class="btn btn-warning"><?php echo $VCM_edit; ?></a>
                                        <a href="index.php?page=storico&squadra=<?php echo $row['nome']; ?>"  class="btn btn-success">Storico</a>
                                        <a href="index.php?page=azioni&name=eliminasquadra&squadra=<?php echo $row['nome']; ?>" class="btn btn-danger"><?php echo $VCM_delete; ?></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>


            </div>
        </div>
    </div>
</div>