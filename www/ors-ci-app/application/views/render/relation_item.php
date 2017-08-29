
<?php

//var_dump($item);
// sort on key alphabettically

foreach ($item as $key => $values) {
print("<ol>\n");
    print("<h4> $key </h4>\n");
    foreach ($values as $value) {
        print("<li>\n");
        foreach ($value as $subkey => $subvalue) {
            print("<p>$subkey : $subvalue\n</p>");
        }
        print("</li>\n");
    }
    
    print("</ol>\n");
}

?>



