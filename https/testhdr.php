<?php

include("../include/globals.php");

foreach( array ( new DotMsgbase("/home/fido/msgbase/netmail"),
                 new JamMsgbase("/home/fido/msgbase/testing"),
                 new JamMsgbase("/home/fido/msgbase/INFO"),
                 new JamMsgbase("/home/fido/msgbase/badmail"),
                 new JamMsgbase("/home/fido/msgbase/dupes")
               ) as $msgbase ){
    
    echo "<hr>Header Testing: ".$msgbase->toString()."<ul>";
    foreach($msgbase->getHeaders() as $hdr )
    //$hdr = $msgbase->getHeader( 1 );
    {
        echo "<div class='message' id='msg-".$hdr['num']."'>";
        echo "<table>";
        echo "<tr><td>Message #".$hdr['num']." @".$hdr['filename'];
        echo "<tr><td>From: <b>".$hdr['fromName']."</b>";
        if($hdr['origAddr']) { 
            echo " @ ".$hdr['origAddr'];
        }
        echo "<tr><td>To: <b>".$hdr['toName']."</b>";
        if ($hdr['destAddr']) {
            echo " @ " . $hdr['destAddr'];
        }
        echo "<tr><td>Subj: <b>".$hdr['subject']."</b>";
        echo "<tr><td>Date: <b>".$hdr['date']."</b>";
        echo "<tr><td>Attr: <b>".MsgBase::attribToString($hdr['attrib'])."</b>";
        echo "</table>";
        echo "</div><hr>";
    }
    echo "</ul>";

}