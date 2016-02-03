<p>
<h3>Open Data</h3></p>
<p>De gehele database in het oorspronkelijke formaat te downloaden. Het is een gecomprimeerd json bestand dat bestaat uit ongeveer 
    4500 hashes met daarin de velden: 'name', 'set' (De instantie), en 'html'. Het html veld bevat de gehele gecrawlde pagina plus de oorspronkelijke http headers.</p>
<ul>
    <?php
    $dir = 'old-data';
    $fds = scandir($dir);

    foreach ($fds as $fd) {
        if ($fd === '.' || $fd === '..') {
            continue;
        }
        ?>
    <li><a href="<?php echo base_url() . $dir . '/' . $fd ?>"><?php echo $fd ?></a></li><?php
}
?></ul>