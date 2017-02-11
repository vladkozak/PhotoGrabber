<?php
/**
 * @package Agere_PhotoGrabber.php
 * @author Vlad Kozak <vk@agere.com.ua>
 */
class Agere_PhotoGrabber_Model_Observer extends Varien_Event_Observer {

    public function runPhotoGrabber () {
        /** @var Agere_PhotoGrabber_Helper_Data $photo */
        $photo = Mage::helper('photoGrabber');
        $photo->syncImages();

       /* $grabber = Mage::helper('photoGrabber/grabber');
        $grabber->start();*/
    }
}