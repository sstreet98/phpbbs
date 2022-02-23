<?php global $USER, $COOKIE; ?>
<!DOCTYPE html>
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php
          echo $TEMPLATEVARS['titleprefix'];
          if (array_key_exists('title', $TEMPLATEVARS)) {
            echo " - " . $TEMPLATEVARS['title'];
          }
          ?></title>
  <link rel="stylesheet" href="/css/bootstrap.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Audiowide">
  <script src="https://code.jquery.com/jquery-3.6.0.js"></script>

  <style>
    #heading {
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      width: 100%;
      height: 80px;
      color: whitesmoke;
      background: #444444;
    }

    #logotext {
      font-family: "Audiowide";
      font-size: 32pt;
      text-shadow: 5px 5px 5px black;
    }

    #copyright {
      font-size: 8pt;
      text-align: left;
      margin-left: 3em;
      float: left;
    }

    #uidtext {
      font-size: 8pt;
      text-align: right;
      padding-right: 3em;
    }

    #uid {
      color: #44FF44;
    }

    #content {
      padding: 1em;
      background: #000;
      color: white;
    }

    #breadcrumbs {
      color: white;
      background: #000;
      padding: 1em;
      border-bottom: grey solid 1px;
    }

    thead,
    tbody,
    tfoot,
    tr,
    td,
    th {
      border-width: 1px;
    }

    body {
      margin: .25em;
    }

    .msgnormal {
      margin: 0;
      padding: 0;
      color: aquamarine;
    }

    .msgkludge {
      display: none;
      margin: 0;
      padding: 0;
      color: chartreuse;
    }

    .msgorigin {
      margin: 0;
      padding: 0;
      color: green;
    }

    .msgquote {
      margin: 0;
      padding: 0;
      color: darkmagenta;
    }
  </style>
</head>

<body style="margin:0;">
  <div id="heading">

    <div style="text-align:center">
      <img style="max-height:80px;left: -10px;position: absolute;" src="/images/earth-left.png">
      <span id="logotext">The Digital Post</span>
      <img style="max-height:80px;right: -7px;position: absolute;" src="/images/moon-right.png">
    </div>

    <div id="copyright">
      &copy;2022 Scott S. Street - All Rights Reserved
    </div>
    <div id="uidtext">
      <?php if ($USER) { ?>
        Welcome, <span id='uid'>
          <?php
          if ($USER->isGuest()) {
            echo "Guest";
          } else {
            echo "<a href='/logout.php'>$USER->name</a>";
          }
          ?></span>, to The Digital Post!
        <?php if ($USER->isGuest()) { ?>
          <a href="/login.php">Login Now</a> or <a href="/signup.php">Signup</a>
        <?php } ?>
      <?php } ?>
    </div>
  </div>