<?php


class Controller {
    
    public function showJson(array $toJson) {
        $json = json_encode($toJson);
        echo $json;
    }
}