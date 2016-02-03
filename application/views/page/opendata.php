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
<p/>
<h3>API voor nevenfuncties</h3>
<p>Open Rechtspraak bevat nu tevens ook een heuse API (application programming interface) voor nevenfuncties. Dit is een RESTFULL api welke twee apicalls ondersteunt:</p>
<p>1. http://ors.openstate.eu/index.php/relations/json geeft een overzicht van alle nevenfuncties met URL's naar de JSON pagina</p>
<p>2. http://ors.openstate.eu/relations/instantie/RECHTBANK/NAAM/json Naam en Rechtbank dienen in URL ENCODE gecodeerd te zijn, cf. de lijst van punt 1.</p>
