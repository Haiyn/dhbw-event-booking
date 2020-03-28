<?php

for ($i = 0; $i < 101; $i++) {
    if ($i % 2 == 0) {
        echo($i . " is even</br>");
    } else {
        echo($i . " is odd</br>");
    }
}

for ($j = 0; $j < 101; $j = $j + 2) {
    echo($j . "</br>");
}
