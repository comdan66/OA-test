<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Site_controller extends Oa_controller {

  public function __construct () {
    parent::__construct ();
    $this->load->helper ('identity');

    $this
         ->set_componemt_path ('component', 'site')
         ->set_frame_path ('frame', 'site')
         ->set_content_path ('content', 'site')
         ->set_public_path ('public')

         ->set_title ("CatMap")

         ->_add_meta ()
         ->_add_css ()
         ->_add_js ()
         ;
  }

  private function _add_meta () {
    return $this->add_meta (array ('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui'))
                ->add_meta (array ('name' => 'robots', 'content' => 'index,follow'))
                ->add_meta (array ('name' => 'author', 'content' => '吳政賢(OA Wu)'))
                ->add_meta (array ('name' => 'keywords', 'content' => 'CatMap | OA Wu | Google maps cats'))
                ->add_meta (array ('name' => 'description', 'content' => '一個搭配 App 的簡單小作品！練習使用基本的 TableView、MKMapView UI.. 等相關，設計出基本的應用 App。Demo 網址：http://catmap.ioa.tw/'))
                ->add_meta (array ('property' => 'og:site_name', 'content' => 'CatMap'))
                ->add_meta (array ('property' => 'og:title', 'content' => 'CatMap'))
                ->add_meta (array ('property' => 'og:description', 'content' => '一個搭配 App 的簡單小作品！練習使用基本的 TableView、MKMapView UI.. 等相關，設計出基本的應用 App。Demo 網址：http://catmap.ioa.tw/'))
                ->add_meta (array ('property' => 'fb:admins', 'content' => '100000100541088'))
                ->add_meta (array ('property' => 'fb:app_id', 'content' => '640377126095413'))
                ->add_meta (array ('property' => 'og:locale', 'content' => 'zh_TW'))
                ->add_meta (array ('property' => 'og:locale', 'content' => 'en_US'))
                ->add_meta (array ('property' => 'og:type', 'content' => 'website'))
                ->add_meta (array ('property' => 'og:image', 'content' => base_url ('resource', 'image', 'og', 'large-compressor.png'), 'alt' => 'CatMap'))
                ->add_meta (array ('property' => 'og:image:type', 'content' => 'image/png'))
                ->add_meta (array ('property' => 'og:image:width', 'content' => '1200'))
                ->add_meta (array ('property' => 'og:image:height', 'content' => '630'))
                ;
  }

  private function _add_css () {
    return $this;
  }

  private function _add_js () {
    return $this->add_js (base_url ('resource', 'javascript', 'jquery_v1.10.2', 'jquery-1.10.2.min.js'))
                ->add_js (base_url ('resource', 'javascript', 'jquery-rails_d2015_03_09', 'jquery_ujs.js'))
                ;
  }
}