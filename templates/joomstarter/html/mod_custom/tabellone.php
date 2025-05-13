<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomstarter\Helpers\Competizione;
use Joomla\CMS\Language\Text;

$user = Factory::getUser();
$userId = $user->id;

if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    if($_GET['module_id'] != 118) {
        header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=118");
        exit;
    }
    $tableStatistiche = Competizione::getTableStatistiche($idcomp);
    $tablePartite = Competizione::getTablePartite($idcomp);
    $competizione = Competizione::getCompetizioneById($idcomp, $userId);
    $mod = $competizione->modalita;
    $gironi = $competizione->gironi;
    // Recupera le squadre
    $squadreJson = $competizione->squadre;
    // Decodifica la stringa JSON in un array
    $squadre = json_decode($squadreJson, true);
    // Recupera le partite
    $partite = Competizione::getPartite($tablePartite); // Funzione da implementare
    $squadre = Competizione::getSquadreOrdinate($squadre);
    // Creiamo un array per memorizzare i risultati
    $risultati = [];
    foreach ($partite as $partita) {
        $risultati[$partita->squadra1][$partita->squadra2] = "{$partita->gol1} - {$partita->gol2}";
    }
    ?>
    <div class="container tabellone">
        <?php if ($mod !== 70): ?>
            <div class="table-responsive my-5">
                <table class="table table-striped table-bordered text-center">
                    <tr>
                        <th><?php echo Text::_('JOOM_NUM_ITEMS'); ?></th>
                        <!-- Spazio per l'angolo in alto a sinistra -->
                        <?php foreach ($squadre as $squadra): ?>
                            <?php
                            $cf = Competizione::getCustomFields($squadra);
                            // Retrieve color values with defaults
                            $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                            $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                            ?>
                            <th style="min-width:50px;">
                                <div class="px-2" style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                    <span style="color:<?php echo $color2; ?>">
                                        <?php echo htmlspecialchars(Competizione::abbreviaNomeSquadra(Competizione::getArticleTitleById($squadra))); ?>
                                    </span>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($squadre as $squadra): ?>
                        <?php
                        $cf = Competizione::getCustomFields($squadra);
                        // Retrieve color values with defaults
                        $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                        $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                        ?>
                        <tr>
                            <th>
                                <div class="px-2" style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                    <span style="color:<?php echo $color2; ?>">
                                        <?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra)); ?>
                                    </span>
                                </div>
                            </th>
                            <!-- Nome della squadra nella prima colonna -->
                            <?php foreach ($squadre as $squadraAvversaria): ?>
                                <?php if ($squadra === $squadraAvversaria): ?>
                                    <td style="background-color: black; color: white;"></td> <!-- Casella nera per le stesse squadre -->
                                <?php else: ?>
                                    <td style="vertical-align:middle;"><?php echo htmlspecialchars($risultati[$squadra][$squadraAvversaria] ?? ''); ?></td>
                                    <!-- Mostra il risultato se disponibile, altrimenti lascia vuota -->
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php else: ?>
            <?php
            for ($i = 1; $i <= $gironi; $i++) {

                // Ottieni le squadre per il girone
                $squadre = Competizione::getSquadreperGirone($tablePartite, $i);

                // Estrai solo i numeri delle squadre
                $squad = array_map(function ($squadra) {
                    return $squadra->squadra;
                }, $squadre);

                // Ottieni le squadre ordinate
                $newsquad = Competizione::getSquadreOrdinate($squad);

                // Crea direttamente l'array di oggetti
                $squadre = array_map(function ($numero) {
                    $obj = new stdClass();
                    $obj->squadra = $numero;
                    return $obj;
                }, $newsquad);

                ?>
                <div class="table-responsive my-5">
                    <h1 class="text-center">Girone <?php echo $i; ?></h1>
                    <table class="table table-striped table-bordered text-center">
                        <tr>
                            <th><?php echo Text::_('JOOM_NUM_ITEMS'); ?></th>
                            <!-- Spazio per l'angolo in alto a sinistra -->
                            <?php foreach ($squadre as $squadra): ?>
                                <?php
                                $cf = Competizione::getCustomFields($squadra->squadra);
                                // Retrieve color values with defaults
                                $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                                $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                                ?>
                                <th>
                                    <div class="px-2" style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                        <span style="color:<?php echo $color2; ?>">
                                            <?php echo htmlspecialchars(Competizione::abbreviaNomeSquadra(Competizione::getArticleTitleById($squadra->squadra))); ?>
                                        </span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($squadre as $squadra): ?>
                            <?php
                            $cf = Competizione::getCustomFields($squadra->squadra);
                            // Retrieve color values with defaults
                            $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                            $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                            ?>
                            <tr>
                                <th>
                                    <div class="px-2" style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                        <span style="color:<?php echo $color2; ?>">
                                            <?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra->squadra)); ?>
                                        </span>
                                    </div>
                                </th>
                                <!-- Nome della squadra nella prima colonna -->
                                <?php foreach ($squadre as $squadraAvversaria): ?>
                                    <?php if ($squadra->squadra === $squadraAvversaria->squadra): ?>
                                        <td style="background-color: black; color: white;"></td> <!-- Casella nera per le stesse squadre -->
                                    <?php else: ?>
                                        <td style="vertical-align:middle;"><?php echo htmlspecialchars($risultati[$squadra->squadra][$squadraAvversaria->squadra] ?? ''); ?>
                                        </td>
                                        <!-- Mostra il risultato se disponibile, altrimenti lascia vuota -->
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php
            }
            ?>
        <?php endif; ?>
    </div>
    <?php
}
?>