<?php

function t($fun): array
{
    $output = [null, null, null];

    try {
        $output[0] = $fun();
    } catch (Exception $e) {
        $output[1] = $e->getMessage();
        $output[2] = $e;
    }

    return $output;
}
