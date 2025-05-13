<?php
// Ottenere lo schema (http o https)
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Ottenere l'host (es. www.esempio.com)
$host = $_SERVER['HTTP_HOST'];

// Ottenere il percorso (es. /pagina.php)
$path = $_SERVER['REQUEST_URI'];

// Comporre l'URL completo
$currentUrl = $scheme . '://' . $host . $path;

/* echo $currentUrl; */

?>
<header class="navbar navbar-expand-xl navbar-dark bg-dark" id="navbar">
    <div class="container">
        <!-- Logo a sinistra -->
        <a class="navbar-brand" href="index.php">
            <img src="img/logo.png" alt="VCM" height="30">
        </a>

        <!-- Menu al centro -->
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=gruppi"><b>Gruppi</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=giocatori"><b><?php echo $VCM_players; ?></b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><b>Nuova Competizione</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=competizioni&com=0"><b><?php echo $VCM_comp_in_progress; ?></b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=competizioni&com=1"><b><?php echo $VCM_comp_finished; ?></b></a>
                </li>
                <?php if (isset($_SESSION['username'])) : ?>
                    <!--                 <li class="nav-item">
                    <a class="nav-link active" href="index.php?page=profilo"><b><?php echo $VCM_profile ?></b></a>
                </li> -->
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?page=logout"><b><?php echo $VCM_logout; ?></b></a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Elementi a destra -->
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['username'])) : ?>
                    <p class="m-0 me-3 fw-bold"><?php echo $VCM_welcome . " " . ucfirst($_SESSION['username']); ?></p>
                <?php endif; ?>

                <div class="d-flex d-flex align-items-center">
                    <!-- Cambio lingua -->
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle px-0 fw-bold" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $VCM_language; ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="index.php?page=azioni&lang=it&url=<?php echo urlencode($currentUrl); ?>"><?php echo $VCM_italian; ?></a>
                            </li>
                            <li><a class="dropdown-item" href="index.php?page=azioni&lang=en&url=<?php echo urlencode($currentUrl); ?>""><?php echo $VCM_english; ?></a>
                            </li>

                            <!-- Aggiungi altre lingue se necessario -->
                        </ul>
                    </div>

                    <!-- Cambio tema chiaro/scuro -->
                    <div class=" form-check form-switch px-3 d-flex align-items-center">
                                    <label class="form-check-label d-flex align-items-center" for="themeSwitch">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-brightness-high-fill" viewBox="0 0 16 16">
                                            <path d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
                                        </svg>
                                    </label>
                                    <input class="form-check-input d-flex align-items-center" type="checkbox" id="tema">
                                    <label class="form-check-label d-flex align-items-center" for="themeSwitch">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16">
                                            <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278" />
                                        </svg>
                                    </label>
                    </div>
                </div>
            </div>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</header>