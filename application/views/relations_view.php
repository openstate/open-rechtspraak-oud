<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li class="active"><a href="<?= site_url('relations'); ?>">Relaties</a></li>
</ul>
<?php

$this->table->set_heading( array('Name', 'Set') );
foreach($json as $element) {
    $link = $this->load->view('render/relatie_link', array( "set" => $element['set'], "name" => $element['name'] ) , true);  
    $this->table->add_row( $link , $element['set'] );
}
echo $this->table->generate();

?>


