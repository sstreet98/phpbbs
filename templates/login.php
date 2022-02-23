<?php
global $TEMPLATEVARS;
global $USER, $password, $uri;
include("header.php");
?>

<div class="msgcntr">
<?php
    if( array_key_exists( 'exception', $TEMPLATEVARS )) {
        echo "<h2>".$TEMPLATEVARS['exception']->getMessage()."</h2>";
    }
?>
</div>
<div id='content'>
    <form name=login method=post>
        Username: <input type='text' name='name'><br>
        Password: <input type='password' name='password'><br>
        <input type='hidden' name='orig_uri' value='<?php echo $uri; ?>'>
        <input type='submit' name='submit' value='Login'>
    </form>
</div>
