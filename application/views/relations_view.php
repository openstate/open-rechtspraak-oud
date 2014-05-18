<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li class="active"><a href="<?= site_url('relations'); ?>">Relaties</a></li>
</ul>
<?php
$this->table->set_heading(array('Name', 'Instantie', 'Link'));
foreach ($json as $element) {
    $link = $this->load->view('render/relatie_link', ["set" => $element['set'], "name" => $element['name']], true);  
    $this->table->add_row(array($element['name'], $element['set'], $link));
}
echo $this->table->generate();

?>


