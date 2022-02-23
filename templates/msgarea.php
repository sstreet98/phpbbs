<?php

global $TEMPLATEVARS, $USER, $COOKIE;
include("header.php");

?>
<div id='breadcrumbs'>
    <a href='/'>Main</a> > <a href='/msgarea.php?area=<?= $TEMPLATEVARS['area']->name ?>'><?= $TEMPLATEVARS['area']->name ?></a>
</div>
<div id='content'>
    <h1><?= $_REQUEST['area'] ?></h1>
    <h2>Has <?= count($TEMPLATEVARS['hdrs']) ?> total messages.</h2>
    <h3>Numbered from <?= $TEMPLATEVARS['firstMsgNum'] ?> to <?= $TEMPLATEVARS['lastMsgNum'] ?></h3>
    <h3><?= $TEMPLATEVARS['visible'] ?> of <?= count($TEMPLATEVARS['hdrs']) ?> are available to you</h3>
    <h3><?= $TEMPLATEVARS['toYou'] ?> are addressed to you</h3>
    <h3><?= $TEMPLATEVARS['toYouUnread'] ?> are waiting for you to read</h3>
    <br>
    <table>
        <tr>
            <th>#
            <th>From
            <th>To
            <th>Subject
            <th>Date
        </tr>
        <?php
        foreach ($TEMPLATEVARS['hdrs'] as $hdr) {
            if (
                ($hdr['attrib'] & MsgArea::ATTRIB_PRIVATE) == 0  ||
                $TEMPLATEVARS['area']->sysopsec <= $USER->security ||
                $hdr['fromName'] == $USER->name ||
                $hdr['toName'] == $USER->name
            ) {
                print "<tr onclick=\"document.location='/message.php?area=" . $TEMPLATEVARS['area']->name . "&num=" . $hdr['num'] . "'\">";
                print "<td>";
                print $hdr['num'];
                print "<td>" . $hdr['fromName'] . "<td>";
                if ($hdr['toName'] == $USER->name) {
                    print "<b>";
                    if (($hdr['attrib'] & MsgArea::ATTRIB_READ) == 0) {
                        print "<i>";
                    }
                }
                print $hdr['toName'];
                if ($hdr['toName'] == $USER->name) {
                    print "</b>";
                    if (($hdr['attrib'] & MsgArea::ATTRIB_READ) == 0) {
                        print "</i>";
                    }
                }
                print "<td>";
                print $hdr['subject'];
                print "<td>";
                print $hdr['date'];
                print "</tr>";
            }
        }
        ?>
    </table>
</div>