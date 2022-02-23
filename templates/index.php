<?php

global $TEMPLATEVARS, $USER, $COOKIE;
include("header.php");

if( array_key_exists('areainfo', $TEMPLATEVARS) && count( $TEMPLATEVARS['areainfo']) > 0 ) {
?>
<ul>
<?php
    foreach( $TEMPLATEVARS['areainfo'] as $mainfo ) { ?>
        <li><a href="msgarea.php?area=<?= $mainfo['area']->name ?>"><?= $mainfo['area']->name ?></a></li>
<?php
    }
?>
</ul>
<?php
}
?>




