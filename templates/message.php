<?php

global $TEMPLATEVARS, $USER, $COOKIE;
include("header.php");
// $TEMPLATEVARS['message']
?>
<div id='breadcrumbs'>
    <a href='/'>Main</a> > <a href='/msgarea.php?area=<?= $TEMPLATEVARS['area']->name ?>'><?= $TEMPLATEVARS['area']->name ?></a>
</div>
<div id='content'>
    <label>From: <?= $TEMPLATEVARS['message']['fromName'] ?></label><br>
    <label>To: <?= $TEMPLATEVARS['message']['toName'] ?></label><br>
    <label>Date: <?= $TEMPLATEVARS['message']['date'] ?></label><br>
    <label>Subject: <?= $TEMPLATEVARS['message']['subject'] ?></label><br>
    <label>Attribute:<?php print MsgArea::attribString( $TEMPLATEVARS['message']['attrib']); ?></label>
    <hr>
<?php
    foreach( $TEMPLATEVARS['message']['body'] as $be ) {
        $pgclass = "msgnormal";
        if( $be[0] == 1 ) {
            $pgclass = "msgkludge";
        } elseif( strncmp( "---", $be[1], 3)==0  || strncmp(" * Origin: ", $be[1], 11) == 0 ) {
            $pgclass = "msgorigin";
        } elseif( preg_match("/(^\s*[A-Za-z]{2,3}>)|(^\s*>).*/s", $be[1]) ){
            $pgclass = "msgquote";
        }
        print "\t<p class='".$pgclass."'>";

        if( strlen($be[1]) > 0 ) {
            print htmlentities($be[1]);
        } else {
            print "&nbsp;"; 
        }
        print "</p>\r\n";
    }
?>
</div>