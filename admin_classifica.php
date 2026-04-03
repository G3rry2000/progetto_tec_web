<?php
session_start();
require_once 'includes/db_connect.php';
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') die("Negato");

if (isset($_POST['update_classifica'])) {
    foreach ($_POST['team'] as $id => $v) {
        pg_query_params($db, "UPDATE classifica SET pt=$1, g=$2, v=$3, n=$4, p=$5, dr=$6 WHERE id=$7",
            [$v['pt'], $v['g'], $v['v'], $v['n'], $v['p'], $v['dr'], $id]);
    }
}

$resC = pg_query($db, "SELECT * FROM classifica ORDER BY pt DESC");
include 'includes/header.php';
?>

<div style="max-width: 900px; margin: 40px auto; padding: 20px; background: #161b22; border-radius: 10px;">
    <h2 style="color:white;">Modifica Manuale Classifica</h2>
    <form method="POST">
        <table style="width:100%; color:white; text-align:left;">
            <tr>
                <th>Squadra</th><th>PT</th><th>G</th><th>V</th><th>N</th><th>P</th><th>DR</th>
            </tr>
            <?php while($c = pg_fetch_assoc($resC)): ?>
            <tr>
                <td><?= $c['squadra'] ?></td>
                <td><input type="number" name="team[<?= $c['id'] ?>][pt]" value="<?= $c['pt'] ?>" style="width:40px;"></td>
                <td><input type="number" name="team[<?= $c['id'] ?>][g]" value="<?= $c['g'] ?>" style="width:40px;"></td>
                <td><input type="number" name="team[<?= $c['id'] ?>][v]" value="<?= $c['v'] ?>" style="width:40px;"></td>
                <td><input type="number" name="team[<?= $c['id'] ?>][n]" value="<?= $c['n'] ?>" style="width:40px;"></td>
                <td><input type="number" name="team[<?= $c['id'] ?>][p]" value="<?= $c['p'] ?>" style="width:40px;"></td>
                <td><input type="number" name="team[<?= $c['id'] ?>][dr]" value="<?= $c['dr'] ?>" style="width:40px;"></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <button type="submit" name="update_classifica" style="background:#2e7d32; color:white; padding:10px; border:none; margin-top:15px;">Aggiorna Classifica</button>
    </form>
</div>