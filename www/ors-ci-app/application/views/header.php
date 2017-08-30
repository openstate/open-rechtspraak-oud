<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">

        <title><?php
            echo 'Open rechtspraak';
            ?></title>
        <!--        /* JQUERY */-->
        <script src="<?php echo base_url() . "public/js/jquery-1.9.0.min.js" ?>"></script>
        <!--        /* JQUERY */-->
        <!--        /* Bootstrap */-->
        <link href="<?php echo base_url() . "public/bootstrap/css/bootstrap.min.css" ?>" rel="stylesheet" media="screen">
        <script src="<?php echo base_url() . "public/bootstrap/js/bootstrap.min.js" ?>"></script>
        <link href="<?php echo base_url() . "public/bootstrap/css/bootstrap.add.min.css" ?>" rel="stylesheet" media="screen">

        <!--        /* Bootstrap */-->
        <!--        /* Other CSS */-->
        <link rel="stylesheet" media="screen" href="<?php echo base_url() . "public/css/css.css" ?>">
    </head>  
    <body>
        <div class="container">
            <div class="masthead">

                <div class="header" >
                    <h3 class="muted" style="
                        display: inline;position: absolute;
                        ">Open Rechtspraak</h3></div>
                <div class="navbar">
                    <div class="navbar-inner">
                        <div class="container">
                            <ul class="nav">
                                <li><a href="<?php echo base_url() ?>index.php">Home</a></li>
                                <li><a href="<?php echo base_url() ?>index.php/rechtspraak/personen">Personen</a></li>
                                <li><a href="<?php echo base_url() ?>index.php/content/page/opendata">Open Data</a></li>
                                <li><a href="<?php echo base_url() ?>index.php/content/page/contact">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div><!-- /.navbar -->
            </div><!-- /.masthead -->
            <div class="content">