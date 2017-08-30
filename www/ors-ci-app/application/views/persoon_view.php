<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li ><a href="<?= site_url(['rechtspraak', 'personen']); ?>">Personen</a><span class="divider">/</span></li>
    <li class="active"><?= $name; ?></li>
</ul>

<?php
echo '<div class="props"><h3>Eigenschappen</h3>';
foreach ($results as $element) {

    foreach ($element['_source']["enrichments_basic_name"] as $key => $value) {
        print($key);
        print (": ");

        if (is_array($value)) {

            foreach ($value as $ele) {
                print($ele);
                print (" ");
            }
            print("</br>");
        } else {
            print($value);
            print("</br>");
        }
    }
    ?></li>            <?php
    break;
}
echo '</div>';
?>
<?php
foreach ($results as $element) {/* staat meerdere posities toe */
    echo "<div class='position'><h3>" . $element['_source']['type'] . "</h3>";
    if (isset($element['_source']['item']['message'])) {
        echo "<p>" . $element['_source']['item']['message'] . "</p>";
    } else {
        $this->load->view('render/persoon_item', array("item" => $element['_source']['item'], true));
    }
    echo "</div>";
}
?>
        




