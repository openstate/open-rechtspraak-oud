<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li class="active"><a href="<?= site_url(['rechtspraak', 'personen']); ?>">Personen</a></li>
</ul>

<?php
$c_results = count($results);
print("Totaal aantal unieke personen $c_results\n</br></br>");

$this->table->set_heading(array('Name', 'Aantal Posities'));

for ($i = 0; $i < $c_results; $i++) {
    $link = $this->load->view('render/persoon_link', array("name" => $results[$i]['key']), true);
    $this->table->add_row($link, $results[$i]['doc_count']);
}
echo $this->table->generate();
?>


