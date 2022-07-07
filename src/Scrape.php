<?php
namespace App;

require 'C:/projects/learnings/web_scrapping/vendor/autoload.php';

class Scrape
{
    private array $products = array();
    protected $dates;

    public function run($pageNo): void
    {
        $document = ScrapeHelper::fetchDocument("https://www.magpiehq.com/developer-challenge/smartphones/?page=$pageNo");
        $document->filter('#products > div.flex.flex-wrap.-mx-4 > div > div')->each(function ($node, $i) {
        });
        $items =  $document->filter('div.flex.flex-wrap.-mx-4 > div > div')->each(function ($node, $i) {
            $imgs = $node->filter('img')->attr('src');
            $newImages = preg_replace( "/^\.+|\.+$/", "", $imgs);
            $images = 'https://www.magpiehq.com/developer-challenge'.$newImages;
            $capacityGb = $node->filter('h3 > span.product-capacity')->text();
            $trimmedGb = str_replace(' ', '', $capacityGb);
            $parsedGb = (int) substr($trimmedGb, 0, strlen($trimmedGb)-2);
            $price = $node->filter('div.my-8.block.text-center.text-lg')->text();
            $colors = $node->filter('div > div > div > span.border.border-black.rounded-full.block')->first()->attr('style');
            $newdata = explode(' ', $colors);
            $myDate = $node->filter('div.my-4.text-sm.block.text-center')->last()->text();
            $statusMessage = explode(" ", $myDate);
            $SliceDate = array_slice($statusMessage, -3, 3);
            $date = implode(" ", $SliceDate);

            if ((int)$date) {
                $convertedDate = (string)$date;
                $newDate = date_create($convertedDate);
                $this->dates = date_format($newDate,"Y-m-d");
            }

            $myArr = array(
                "imageUrl" => $images,
                "title" => $node->filter('h3 > span.product-name')->text(),
                "capacityMb" => $parsedGb*1024 ."MB",
                "price" => substr($price, 2),
                "availabilityText" => $node->filter('div.my-4.text-sm.block.text-center')->first()->text(),
                "isAvailable" => $node->filter('div.my-4.text-sm.block.text-center')->first()->text() == "Availability: Out of Stock" ? false : true,
                "shipText" => $node->filter('div.my-4.text-sm.block.text-center')->last()->text(),
                "shipdate" => $this->dates,
                "color" => $newdata[5],
            );

            //Saving it inside the file

            if (filesize("output.json") && filesize("output.json") == 0) {
                $first_record = array($myArr);
                $data_to_save = $first_record;
            } else {
                $old_records = json_decode(file_get_contents("output.json")) ? json_decode(file_get_contents("output.json")) : array();

                array_push($old_records, $myArr);
                $data_to_save = $old_records;
            }
file_put_contents("output.json", json_encode($data_to_save, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
           
    });

    }

}

$scrape = new Scrape();
for ($i=1; $i <= 3; $i++) { 
    $scrape->run($i);
}
