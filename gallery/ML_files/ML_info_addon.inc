
<!-- Language / Locale : <?php echo $nls['languages'][$gallery->ML->language] ; ?> / <?php echo $gallery->ML->locale ; ?> -->
<!-- Charset  : <?php echo $gallery->ML->charset ; ?> -->
<!-- Direction : <?php echo $gallery->ML->direction ; ?> -->

<?php
        if ($gallery->ML->error) {
                if ($gallery->ML->ignore_errors == 'no') {
                        echo "<p style=\"color:red\">". $gallery->ML->error ."</p>\n";
                } else {
                        echo "<!-- Error : " . $gallery->ML->error . "-->";
                }
        }
?>
<!-- OS : <?php echo PHP_OS; ?> -->