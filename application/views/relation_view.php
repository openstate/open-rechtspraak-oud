<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li ><a href="<?= site_url('relations'); ?>">Relaties</a><span class="divider">/</span></li>
    <li class="active"><?= $json['name']; ?></li>
</ul>
<h3><?= $json['name']; ?></h3>
<p><em><?= $json['set']; ?></em></p>
<?php
$doc = new DOMDocument();
@$doc->loadHTML($json['html']);
$xpath = new DOMXpath($doc);

$elements = $xpath->query("//div[@class='details']");
if ($elements->length == 1) {
    echo $doc->saveHTML($elements->item(0));
} else {
    error_log("Page didn't contain div@[class=details]");
    echo "<i>Foutmelding aan 112: HTML data bevat geen div@[class=details]</i>";
}

?>


