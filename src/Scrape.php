<?php

namespace App;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    public function run(): void
    {   
        $url = "https://www.magpiehq.com/developer-challenge/smartphones/";
        //getting pagination
        for ($i=0; $i < 3 ; $i++) { 
            $document[$i] = ScrapeHelper::fetchDocument($url.'?page='.$i+1);
        }
        $this->products = Product::getAllProducts($document); 
        file_put_contents('output.json', str_replace("\/", "/",json_encode($this->products, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
    }
}

$scrape = new Scrape();
$scrape->run();
