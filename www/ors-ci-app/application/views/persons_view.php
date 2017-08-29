<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li class="active"><a href="<?= site_url(['relations', 'persons']); ?>">Personen</a></li>
</ul>
<div>
    <ul>
        <li><a href="<?= site_url(['relations', 'persons']); ?>">Obv. huidige functies</a></li>
        <li><a href="<?= site_url(['relations', 'persons', 'unfiltered']); ?>">Obv. huidige en/of voormalige functies</a></li>            
    </ul>

</div>

<?php
$c_results = count($results);
print("Totaal aantal unieke personen $c_results\n</br></br>");

$this->table->set_heading(array('Name', 'Posities / Gerechtshoven'));

for ($i = 0; $i < $c_results; $i++) {
    $link = $this->load->view('render/person_link', array("name" => $results[$i]['key']), true);
    $this->table->add_row($link, $results[$i]['doc_count']);
}
echo $this->table->generate();
?>


