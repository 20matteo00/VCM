<div class="d-block d-md-flex justify-content-around mb-5">
    <a href="index.php?page=visualizza&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>" class="btn btn-success my-2">Calendario</a>

    <a href="index.php?page=classifica&type=general&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>" class="btn btn-success my-2">Classifica</a>
    <?php if ($mod != "eliminazione") { ?>
        <a href="index.php?page=tabellone&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>" class="btn btn-success my-2">Tabellone</a>
    <?php } ?>
    <a href="index.php?page=statistiche&type=general&name=<?php echo $name; ?>&mod=<?php echo $mod; ?>&tabpar=<?php echo $tablepartite; ?>&tabstat=<?php echo $tablestatistiche; ?>" class="btn btn-success my-2">Statistiche</a>

    <?php /* if (isset($finita)) { ?>
        <?php if ($finita == 0) { ?>
            <a href="index.php?page=azioni&name=chiudi&competizione=<?php echo $name; ?>" class="btn btn-success my-2">Chiudi</a>
        <?php } else if ($finita == 1) { ?>
            <a href="index.php?page=azioni&name=riapri&competizione=<?php echo $name; ?>" class="btn btn-success my-2">Riapri</a>
        <?php } ?>
    <?php }  */?>
</div>