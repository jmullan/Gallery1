<div class="g-stepbar left floatleft">
<?php
    $nr = 0;
    foreach ($steps as $key => $stepText) {
        if(!empty($stepText)) {
            $nr++;
            $current = ($key == $setup_page) ? ' g-current-step' : '';

            echo "\n\t<div class=\"g-step $current\">$stepText</div>";
        }
    }
    if($setup_page != 'constants' && $setup_page != 'defaults') {
        echo gImage('galleryLogo_sm.gif');
    }
?>
</div>