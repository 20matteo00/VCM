<?php
// Messaggi di successo o errore
$success_message = '';
$error_message = '';

$user = $_SESSION['username'];

// Verifica se il modulo è stato inviato per l'aggiunta di un nuovo gruppo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $nome_gruppo = ucwords(strtolower(trim($_POST['nome_gruppo'])));
    $colore_primario = $_POST['colore_primario'];
    $colore_secondario = $_POST['colore_secondario'];

    $query_gruppo = "INSERT INTO gruppi (utente, nome, colore1, colore2) VALUES (?, ?, ?, ?)";
    $stmt_gruppo = $conn->prepare($query_gruppo);
    $stmt_gruppo->bind_param("ssss", $user, $nome_gruppo, $colore_primario, $colore_secondario);
    $stmt_gruppo->execute();
    $stmt_gruppo->close();


    // Reindirizza alla stessa pagina per evitare duplicati
    header("Location: index.php?page=gruppi");
    exit;
}

// Recupera i gruppi e le squadre associate
$query_gruppi = "SELECT * FROM gruppi WHERE utente = ?";
$stmt_gruppi = $conn->prepare($query_gruppi);
$stmt_gruppi->bind_param("s", $user);
$stmt_gruppi->execute();
$result_gruppi = $stmt_gruppi->get_result();
$stmt_gruppi->close();

?>

<div class="container">
    <div class="row justify-content-center">
        <h2 class="text-center mt-5">Gestione Gruppi</h2>
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
            <div class="row align-items-center justify-content-center">
                <div class="col-lg-4 text-center">
                    <div class="mb-3">
                        <label for="nome_gruppo" class="form-label">Nome Gruppo</label>
                        <input type="text" class="form-control" id="nome_gruppo" name="nome_gruppo" required>
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
                        <button type="submit" class="btn btn-success w-100 mt-4" name="submit">Aggiungi Gruppo</button>
                    </div>
                </div>
            </div>
        </form>
        <!-- Elenco dei gruppi -->
        <div class="row mt-5 justify-content-center">
            <div class="col-auto table-responsive">
                <h3 class="text-center">Elenco Gruppi</h3>
                <table class="table table-bordered text-center table-responsive miatable" id="myTable">
                    <thead>
                        <tr>
                            <th class="mioth">Gruppo</th>
                            <th><?php echo $VCM_actions; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($result_gruppi)) : ?>
                            <?php while ($row = $result_gruppi->fetch_assoc()) : ?>
                                <tr>
                                    <td>
                                        <div class="miocolore px-2" style="background-color: <?php echo htmlspecialchars($row['colore1']); ?>; color: <?php echo htmlspecialchars($row['colore2']); ?>;">
                                            <?php echo htmlspecialchars($row['nome']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="index.php?page=azioni&name=modificagruppo&gruppo=<?php echo htmlspecialchars($row['nome']); ?>" class="btn btn-warning"><?php echo $VCM_edit; ?></a>
                                        <a href="index.php?page=azioni&name=eliminagruppo&gruppo=<?php echo htmlspecialchars($row['nome']); ?>" class="btn btn-danger"><?php echo $VCM_delete; ?></a>
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