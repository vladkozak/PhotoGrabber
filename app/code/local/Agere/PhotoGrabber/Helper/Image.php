<?php

/**
 * @package Agere_PhotoGrabber.php
 * @author Vlad Kozak <vk@agere.com.ua>
 */
class Agere_PhotoGrabber_Helper_Image extends Mage_Core_Helper_Abstract {

	/**
	 * This value will be name image.
	 */
	public $imageName;

	/**
	 * @var array
	 */
	protected $specialCharsMap = array(
		//'\\ ' => ' ',
		'&Slash&'        => '/',
		'&Backslash&'    => '\\',
		'&Asterisk&'     => '*',
		'&Pipe&'         => '|',
		'&Colon&'        => ':',
		'&quot&'         => '"',
		'&lt&'           => '<',
		'&gt&'           => '>',
		'&Questionmark&' => '?',
	);

	/**
	 * This function calls image
	 * @param $x
	 * @return $this->imageName;
	 */
	public function getImageName($x) {
		switch ($x) {
			case 0 :
				return $this->imageName = 'main.jpg';
				break;
			default :
				return $this->imageName = $x . '.jpg';
		}
	}

	/**
	 * The function starts to download images
	 *
	 * @param $imageUrl
	 */
	public function run($imageUrl) {
		$count = 0;
		foreach ($imageUrl as $key => $value) {
			if ($count == 0) {
				$base = $key;
			} else {
				$base = null;
			}
			for ($i = 0; $i < count($value); $i++) {
				$this->prepareToDownload($value[$i], $this->getImageName($i), $this->specialCharsEncode($key), $this->specialCharsEncode($base));
			}
			$count++;
		}
	}

	/**
	 * The function to start download images in folder
	 *
	 * @param $imageUrl
	 * @param $filename
	 * @param $art
	 * @param $defaultArt
	 */
	public function prepareToDownload($imageUrl, $filename, $art, $defaultArt) {
        if (trim($imageUrl)) {
            $nameDirectory = Mage::getBaseDir('base') . '/media/import/images';
            $artEnd = explode('&Slash&', $art);
            $nameFolder = $artEnd[0] . '&Slash&' . $artEnd[1];
            $nameSubFolder = $artEnd[2];

            $this->createFolder($nameDirectory, $nameFolder, $nameSubFolder);

            $pathMain = $nameDirectory . '/' . $nameFolder . '/' . $filename;
            $path = $nameDirectory . '/' . $nameFolder . '/' . $nameSubFolder . '/' . $filename;

            if ($defaultArt == $art) {
                $this->download($path, $imageUrl);
                $this->download($pathMain, $imageUrl);
            } else {
                $this->download($path, $imageUrl);
            }
        }
	}

	/**
	 * Check and create folders
	 * @param $nameDirectory
	 * @param $nameFolder
	 * @param $nameSubFolder
	 */
	public function createFolder($nameDirectory, $nameFolder, $nameSubFolder) {
		if (!file_exists($nameDirectory)) {
			mkdir($nameDirectory, 0777, true);
		}
		if (!file_exists($nameDirectory . '/' . $nameFolder)) {
			mkdir($nameDirectory . '/' . $nameFolder, 0777);
		}
		if (!file_exists($nameDirectory . '/' . $nameFolder . '/' . $nameSubFolder)) {
			mkdir($nameDirectory . '/' . $nameFolder . '/' . $nameSubFolder, 0777);
		}
	}

	/**
	 * This function download photos in folders
	 * @param $path
	 * @param $imageUrl
	 */
	public function download($path, $imageUrl) {
		if (!file_exists($path)) {
			file_put_contents($path, file_get_contents($imageUrl));
		}
	}

	/**
	 * @return mixed
	 */
	public function specialCharsReplace() {
		static $mapped = array();

		if (!isset($mapped['from'])) { // optimize code
			$mapped['from'] = array_keys($this->specialCharsMap);
			$mapped['to'] = array_values($this->specialCharsMap);
		}

		return str_replace($mapped['from'], $mapped['to'], $this);
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function specialCharsEncode($name) {
		static $mapped = array();

		if (!isset($mapped['from'])) {
			$mapped['to'] = array_keys($this->specialCharsMap);
			$mapped['from'] = array_values($this->specialCharsMap);
		}

		return str_replace($mapped['from'], $mapped['to'], $name);
	}
}