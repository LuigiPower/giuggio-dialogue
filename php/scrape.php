<?php
/**
 * IMDB quick'n'dirty scraper, just for some info
 */

require_once("utility.php");
require_once("simple_html_dom.php");

$url = $_POST["toscrape"];

$html = file_get_html($url);

//TODO fill with scraped info
$result = array(
    "poster" => ""
);

foreach($html->find('img') as $element)
{
    if(strpos($element->alt, 'Poster') !== false)
    {
        $result['poster'] = $element->src;
    }
}

wrapAndShowJSON(200, true, $result);

?>
