<!DOCTYPE html>
<html>

<head>
    <?php include "layout/head.php"; ?>
</head>

<body>
    <?php
    ob_start();
    include "conn.php";
    session_start();

    // Impostazione della lingua
    if (isset($_SESSION['lang'])) {
        if ($_SESSION['lang'] === 'it') {
            include "language/italiano.php";
        } elseif ($_SESSION['lang'] === 'en') {
            include "language/inglese.php";
        }
    } else {
        include "language/italiano.php";
    }

    $page = isset($_GET['page']) ? $_GET['page'] : '#';
    ?>

    <?php include "layout/header.php"; ?>

    <?php if (isset($_SESSION['username'])) : ?>
        <?php if ($page == "logout") : ?>
            <?php include "logout.php"; ?>
        <?php elseif ($page == "gruppi") : ?>
            <?php include "gruppi.php"; ?>
        <?php elseif ($page == "giocatori") : ?>
            <?php include "giocatori.php"; ?>
        <?php elseif ($page == "azioni") : ?>
            <?php include "azioni.php"; ?>
        <?php elseif ($page == "storico") : ?>
            <?php include "storico.php"; ?>
        <?php elseif ($page == "modalita") : ?>
            <?php include "modalita.php"; ?>
        <?php elseif ($page == "competizioni") : ?>
            <?php include "competizioni.php"; ?>
        <?php elseif ($page == "visualizza") : ?>
            <?php include "competizioni/visualizza.php"; ?>
        <?php elseif ($page == "classifica") : ?>
            <?php include "competizioni/classifica.php"; ?>
        <?php elseif ($page == "tabellone") : ?>
            <?php include "competizioni/tabellone.php"; ?>
        <?php elseif ($page == "statistiche") : ?>
            <?php include "competizioni/statistiche.php"; ?>
        <?php elseif ($page == "fasefinale") : ?>
            <?php include "competizioni/fasefinale.php"; ?>
        <?php else : ?>
            <?php include "home.php"; ?>
        <?php endif; ?>
    <?php else : ?>
        <?php include "login.php"; ?>
    <?php endif; ?>

    <?php ob_end_flush(); ?>
</body>

</html>