<?php
/**
 * @package Agere_PhotoGrabber.php
 * @author Vlad Kozak <vk@agere.com.ua>
 */
require_once __DIR__ . '/../lib/goutte.phar';
use Goutte\Client;

class Agere_PhotoGrabber_Helper_Grabber extends Mage_Core_Helper_Abstract {

    /**
     * @var Goutte\Client
     */
    protected $client;

    /**
	 * This value includes article of Product
     * @var string
     */
    protected $article;

    /**
	 * This value includes sku and urls only of one Product
     * @var array strings
     */
    protected $images;

    /**
	 * This value includes Date when the module does not find SKU
     * @var dataTime
     */
    protected $dateTime;

    /**
     * @param domain;
     * @param client;
     * @param dateTime
     */
    public function __construct() {
		$this->domain = 'http://www.oodji.com';
		$this->client = $this->createClient();
	    $this->dateTime =  Mage::getModel('core/date')->date('Y-m-d_H-i-s');
    }



    /**
     * This function was created for test this module.
     */
      public function start() {
		$mas = array(
			/*'14707006/17482/1200N',
			'14707006/17482/2900N',*/
		);

		foreach ($mas as $sku) {
			$this->searchProduct($sku);
		}
	}

    /**This function generates Urls of Products
     * @param $sku
     */
    public function searchProduct($sku) {
        $basePath = Mage::getBaseDir('base');
		$crawler = $this->callPage($sku);
		$statusCode = $this->client->getResponse()->getStatus();

        if ($statusCode != 200) {
            $this->recreateClient();
            $crawler = $this->callPage($sku);
        }

		$nodeValues = $crawler->filter('.catalog-list #catalog-section .catalog-section-items-list .catalog-section-item a')
			->each(function ($node) {
				return $node->getNode(0)->getAttribute('href');
			});

		if (empty($nodeValues)) {
			file_put_contents($basePath . "/var/import/notFoundSku_{$this->dateTime}.txt", $sku . "\n", FILE_APPEND);
		} else {

            foreach ($nodeValues as $value) {

                if (substr($value, 0, 1) === '/') {
                    $urlPrepared = $this->domain . $value;
                    $this->searchUrlPhotos($urlPrepared);
                }
            }
        }
	}

    /**
	 * @param $sku
     * @return $crawler
     */
    public function callPage($sku) {
        $html = $this->domain . '/ajax/page_search_full.php?q=' . $sku;
        return $crawler = $this->client->request('GET', $html);
    }

	public function createClient() {
		return new Client();
	}

	/**
     * This function unset and creates new Client when statusCode != 200
     */
    public function recreateClient() {
        unset ($this->client);
        $this->client = $this->createClient();
    }

    /**This function generates photos array
     * @param $url
     */
    public function searchUrlPhotos($url) {
        try {
            $crawler = $this->client->request('GET', $url);
            $art = $this->getImageArticle($url);
            $crawler->filter('.catalog-item-images .small-images a')->each(function ($node) use ($art) {
                $imageUrls = json_decode($this->fixJson($node->attr('rel')), true);
                preg_match('/img([A-Za-z0-9]+)/', $node->attr('class'), $artEnd);
                $artArray = explode('/', $art);
                $article = $artArray[0] . '/' . $artArray[1] . '/' . $artEnd[1];
                $this->images[$article][] = $this->domain . $imageUrls["largeimage"];
            });

            /** @var Agere_PhotoGrabber_Helper_Image $img */
            $img = Mage::helper('photoGrabber/image');
            $img->run($this->images);

            unset($this->images);
        }
        catch (Exception $e) {

        }
	}

    /**
     * @param $url
     * @return string
     */
    public function getImageArticle($url) {
		$crawler = $this->client->request('GET', $url);
		$crawler->filter('.catalog-item-description .item-description .PropArticle nobr')->each(function ($node) {
			$this->article = $node->text();
		});

		return $this->article;
	}

    /**This function helps to get
     * @param $a
     * @return mixed
     */
    public function fixJson($a) {
		$a = preg_replace('/(,|\{)[ \t\n]*(\w+)[ ]*:[ ]*/', '$1"$2":', $a);
		$a = preg_replace('/":\'?([^\[\]\{\}]*?)\'?[ \n\t]*(,"|\}$|\]$|\}\]|\]\}|\}|\])/', '":"$1"$2', $a);

		return $a;
	}

}