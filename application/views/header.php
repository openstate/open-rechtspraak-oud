<!DOCTYPE html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <?php // @todo: Facebook linkability
//            echo '<meta property="og:title" content="' . 'Stem voor ' . $partijlid['naam'] . ' op de Hackathon Amsterdam van 29 juni' . ' " />';
//            echo '<meta property="og:description" content="' . 'Like deze pagina en zet ' . $partijlid['naam'] . ' op de stoel van de burgemeester van Amsterdam' . '" />';
//            echo '<meta property="og:image" content="' . base_url(array('public', 'vote.png')) . '" />';
        ?>
        <title><?php
            echo 'Toeval of Niet Rechtspraak';
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
                        ">Toeval of Niet Rechtspraak</h3></div>
                <div class="navbar">
                    <div class="navbar-inner">
                        <div class="container">
                            <ul class="nav">
                                <li><a href="<?php echo base_url() ?>index.php">Home</a></li>
                                <li><a href="<?php echo base_url() ?>index.php/relations">Relaties</a></li>
                                <li><a href="<?php echo base_url() ?>index.php/content/page/contact">Contact</a></li>
                            <!--<li><a href="<?php echo base_url() ?>index.php/test">Test</a></li>-->
                            </ul>
                        </div>
                    </div>
                </div><!-- /.navbar -->
            </div><!-- /.masthead -->
            <div class="content">