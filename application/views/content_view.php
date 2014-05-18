<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li class="active"><a href="<?= base_url(); ?>index.php">Home</a></li>

</ul>
<!--  row of columns -->
<div class="row-fluid">
    <div class="span12">
        <?php $this->load->view('page/' . $page);
        
        ?>


    </div>
</div>


