<?php

function pluralize_n($amt, $one, $more, $none) {
        switch ($amt) {
                case 0 :
                        return $none;
                        break;
                case 1 :
                        return "$amt $one";
                        break;

                default :
                        return "$amt $more";
                        break;
        }
}
?>