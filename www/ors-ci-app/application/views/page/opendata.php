<p>
<h3>Open Data</h3></p>
<p>De gehele database in het oorspronkelijke formaat te downloaden. Het is een gecomprimeerd json bestand dat bestaat uit ongeveer 
    4500 functies waargenomen. De data bevat de gehele gecrawlde pagina plus de oorspronkelijke http headers.</p>
<ul>
    <?php
    
    $dotm = (date('d'));
     //echo $dotm;
    $dir = 'backups/' . $dotm;
    $fds = scandir($dir);

    foreach ($fds as $fd) {
        if ($fd === '.' || $fd === '..') {
            continue;
        }
        $dump = 'fulldump_rechtspraak_e.tar.gz';
        if ($fd != $dump)  continue;
        
        ?>
    <li><a href="<?php echo base_url() . $dir . '/' . $fd ?>"><?php echo $fd ?></a></li><?php
}
?></ul>
<p/>
