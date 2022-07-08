<?php

namespace App;
use Symfony\Component\DomCrawler\Crawler;

class Product
{
    public static function getAllProducts($document){
        $allProducts = [];

        if ($document) {
            foreach ($document as $key => $currentDocument) {
                $currentDocument->filter("div.flex-wrap div.product")->each(function(Crawler $node, $index) use(&$allProducts, &$getCurentColor){
                    $allProducts[] = [
                        "title" => $node->filter("span.product-name")->text(""),
                        "price" => str_replace("£","",$node->filter("div.text-lg")->text("")),
                        "image" => str_replace("../",'',"https://www.magpiehq.com/developer-challenge/".$node->filter("img")->attr('src')),
                        'capacityMB' => Product::getCapacityInMB($node->filter("span.product-capacity")->text("")),
                        'colour' => Product::getFirstColour($node->filter("div.px-2 span.rounded-full")),
                        'availabilityText'=> $availabilityText = str_replace("Availability:","", $node->filter("div.text-sm")->text("")),
                        'isAvailable'=> ( str_contains($availabilityText, "Out of Stock") ) ? false : true ,
                        'shippingText'=> ($node->filter("div.text-sm")->last()->text("") == "Availability: Out of Stock") ? ""  : $node->filter("div.text-sm")->last()->text(""),
                        'shippingDate'=> $node->filter("div.text-sm")->last()->text(""),
                    ];
                });
            }
        }
        $allProducts = Product::getDuplicateColor($allProducts);
        return  array_values(array_unique($allProducts,SORT_REGULAR));
    }

    private static function getCapacityInMB(string $capacity){
        $capacityMb = "";
        if (substr($capacity, -2) == "GB") { 
            $trimmedGb = str_replace(' ', '', $capacity);
            $parsedGb = (int) substr($trimmedGb, 0, strlen($trimmedGb)-2);
            $capacityMb = $parsedGb*1024 ."MB";
        }else{
            $capacityMb = (string)filter_var($capacity); //getting string from value given
        }

        return $capacityMb; 
    }

    private static function getFirstColour($currentNode){
     
        $colour = [];
        $currentNode->each(function(Crawler $node, $index) use(&$colour){
            $colour[] = $node->filter("span.rounded-full")->attr("data-colour");
        });
        return $colour ;

}

    public static function getDate(string $shippingDate){
        $date = "";
        if (str_contains($shippingDate , "Jul") || str_contains($shippingDate , "Aug")) { 
            $month = "";
            $day = "";
            $contain_date = substr($shippingDate, -15);
            $mainDate = strstr($contain_date," ");
            $res = str_ireplace( array( '\'', 'on', 'it', 'by' ), ' ', $mainDate);
            $newDate = date_create($res);
            $date = date_format($newDate,"Y-m-d");
        if (str_contains($shippingDate , "tomorrow")) {
            $tomorrow = strtotime("+1 day");
            //Format the timestamp into a date string
            $date = date("Y-m-d", $tomorrow);
        }
    }
        return $date;
    }

    private static function getDuplicateColor($products){
        $new_color_products = [];

        if ($products) {
            foreach ($products as $products_value) {
                if($products_value['colour']){
                    foreach ($products_value['colour'] as $new_products_value) {
                        $new_color_products[] = [
                            'title' => $products_value['title'],
                            'price' => $products_value['price'],
                            'image' => $products_value['image'],
                            'capacityMB' => $products_value['capacityMB'],
                            'colour' => $new_products_value,
                            'availabilityText'=> $products_value['availabilityText'],
                            'isAvailable'=> $products_value['isAvailable'],
                            'shippingText'=> $products_value['shippingText'],
                            'shippingDate'=>Product::getDate( $products_value['shippingDate']),
                        ];
                    }
                }
            }
        }
        return $new_color_products;
    }
    
}
