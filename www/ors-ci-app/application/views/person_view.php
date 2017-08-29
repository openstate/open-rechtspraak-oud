<!-- Breadcrumbs -->
<ul class="breadcrumb">
    <li><a href="<?= base_url(); ?>index.php">Home</a> <span class="divider">/</span></li>
    <li ><a href="<?= site_url(['relations', 'persons']); ?>">Personen</a><span class="divider">/</span></li>
    <li class="active"><?= $name; ?></li>
</ul>

<div class="meta">
    <h3>Eigenschappen</h3>
    <?php
    foreach ($results as $element) {
        ?><?php
        // var_dump($element['_source']["enrichments"]);die();
        //['_source']["enrichments"]

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
?>


</div>





<div class="positions">
    <h3>Posities</h3>
    <ul><?php
        foreach ($results as $element) {
            ?><li><?php
                // var_dump($element['current']);die();

                echo $element["_source"]["type"];
                if ($element['current'] == true) {
                    echo ' (huidige functie)';
                } else {
                    echo ' (vroegere functie dd. ' . substr($element['_source']['updated'], 0, 10) . ')';
                }
                ?></li>
            <?php
        }
        ?>

    </ul>
</div>


<?php
foreach ($results as $element) {/* staat meerdere hits toe */

// print("<h3>". ($element['_source']['id']) . "</h3> ");
    ?>
    <h3><?= $element['_source']['type']; ?></h3>


                <!--<p><em><?php //$element['_id'];             ?></em></p>-->
        <!--    <p>Oudste versie: <em><?php //$element['_source']['inserted'];   ?></em></p>-->
        <!--    <p>Recentste versie: <em><?php //$element['_source']['updated'];  ?></em></p> -->
    <?php
    if ($element['_source']['previous'] == null) {
        //echo "<p><em>Geen andere versies dan dit document beschikbaar:</em></p>";
    }

    if (isset($element['_source']['item']['message'])) {
        echo "<p>" . $element['_source']['item']['message'] . "</p>";
    } else {
        $this->load->view('render/relation_item', array("item" => $element['_source']['item'], true));
    }
    ?>
    <p><em><?php
            if ($element['_source']['previous'] != null) {
                foreach ($element['_source']['previous'] as $prev) {
                    //    var_dump($prev['diff']);//die();
                    //if ($prev['diff']== null ) continue;
                    echo "<h4>Afwijkingen in Oudere versie van " . $prev['updated'] . "</h4>";
                    if (isset($prev["previous_item"]['message'])) {
                        echo "<p>" . $prev["previous_item"]['message'] . "</p>";
                    } else {
                        $this->load->view('render/relation_item', array("item" => $prev["diff"], true));
                        //$this->load->view('render/relation_item', array("item" => $prev["previous_item"], true));
                    }
                }
            }
            ?></p></em>

    <?php
}
?>
        




