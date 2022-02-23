<?php
require_once("../include/globals.php");
$COOKIE = new Cookie( );
$COOKIE->validate();
?>
<table>
    <tr>
        <th>ID
        <th>Name
    </tr>
    <tr>
        <td><?= $COOKIE->id() ?>
        <td><?= User::getByID( $COOKIE->id() )->name ?>
    </tr>
</table>