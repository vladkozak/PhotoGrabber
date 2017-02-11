<?php
/**
 * Photo Grabber Controller
 *
 * @category Agere
 * @package Agere_PhotoGrabber
 * @author Vlad Kozak <vk@agere.com.ua>
 */

class Agere_PhotoGrabber_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        /** @var Agere_PhotoGrabber_Helper_Data $photo */
        $grabber = Mage::helper('photoGrabber/grabber');
        $grabber->start();
    }

    public function testAction() {

        $grab = Mage::helper('photoGrabber/backup');
        $grab->run();
    }
}