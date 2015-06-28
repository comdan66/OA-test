<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class V1 extends Api_controller {

  public function __construct () {
    parent::__construct ();
  }
  private function _method ($method) {
    return $_SERVER['REQUEST_METHOD'] !== strtoupper ($method) ? 'Request Method Error！' : '';
  }
  private function _error ($message) {
    return $this->output_json (array (
        'status' => false,
        'message' => $message
      ));
  }
  private function _position_format ($picture) {
    if (!(isset ($picture->latitude) && isset ($picture->longitude) && isset ($picture->altitude) && ($picture->latitude != '') && ($picture->longitude != '') && ($picture->altitude != '')))
      return array ();
    else
      return array (
          'latitude' => $picture->latitude,
          'longitude' => $picture->longitude,
          'altitude' => $picture->altitude
        );
  }
  private function _color_format ($picture) {
    if (!(isset ($picture->color_red) && isset ($picture->color_green) && isset ($picture->color_blue) && ($picture->color_red != '') && ($picture->color_green != '') && ($picture->color_blue != '')))
      return array ();
    else
      return array (
          'red' => $picture->color_red,
          'green' => $picture->color_green,
          'blue' => $picture->color_blue
        );
  }
  private function _picture_format ($picture, $size = '800w') {
    $return = array (
        'id' => $picture->id,
        'description' => $picture->description,
        'url' => $picture->name->url ($size),
        'gradient' => $picture->gradient,
        'like_count' => count ($picture->likes),
        'comment_count' => count ($picture->comments),
        'user' => $this->_user_format ($picture->user, '140x140c'),
        'created_at' => $picture->created_at->format ('Y年m月d日 H:i')
      );

    if ($position = $this->_position_format ($picture))
      $return['position'] = $position;

    if ($color = $this->_color_format ($picture))
      $return['color'] = $color;

    return $return;
  }
  private function _user_format ($user, $size = '') {
    $return = array (
        'id' => $user->id,
        'name' => $user->name,
        'account' => $user->account,
        'avatar' => $user->avatar->url ($size),
      );

    if ($color = $this->_color_format ($user))
      $return['color'] = $color;

    return $return;
  }
  public function test () {
    return $this->output_json (array (
        'method' => $_SERVER['REQUEST_METHOD'],
        'gets' => $_GET,
        'posts' => $_POST,
        'files' => $_FILES
      ));
  }

  public function prev_pictures () {
    if ($message = $this->_method ('GET'))
      return $this->_error ($message);

    $prev_id = ($prev_id = $this->input_get ('prev_id')) ? $prev_id : 0;
    $limit = ($limit = $this->input_get ('limit')) ? $limit : 5;

    $conditions = array ('id >= ?', $prev_id);
    $pictures = Picture::find ('all', array ('order' => 'id ASC', 'limit' => $limit + 1, 'include' => array ('user', 'likes', 'comments'), 'conditions' => $conditions));

    $prev_id = ($temp = (count ($pictures) > $limit ? end ($pictures) : null)) ? $temp->id : -1;

    $that = $this;
    return $this->output_json (array (
      'status' => true,
      'pictures' => array_map (function ($picture) use ($that) {
        return $that->_picture_format ($picture);
      }, array_slice ($pictures, 0, $limit)),
      'prev_id' => $prev_id
    ));
  }

  public function next_pictures () {
    if ($message = $this->_method ('GET'))
      return $this->_error ($message);

    $next_id = $this->input_get ('next_id');
    $limit = ($limit = $this->input_get ('limit')) ? $limit : 5;

    $conditions = $next_id ? array ('id <= ?', $next_id) : array ();
    $pictures = Picture::find ('all', array ('order' => 'id DESC', 'limit' => $limit + 1, 'include' => array ('user', 'likes', 'comments'), 'conditions' => $conditions));

    $next_id = ($temp = (count ($pictures) > $limit ? end ($pictures) : null)) ? $temp->id : -1;

    $that = $this;
    return $this->output_json (array (
      'status' => true,
      'pictures' => array_map (function ($picture) use ($that) {
        return $that->_picture_format ($picture);
      }, array_slice ($pictures, 0, $limit)),
      'next_id' => $next_id
    ));
  }

  public function create_picture () {
    if ($message = $this->_method ('POST'))
      return $this->_error ($message);

    $user_id     = $this->input_post ('user_id');
    $description = trim ($this->input_post ('description'));
    $latitude    = (($latitude = trim ($this->input_post ('latitude'))) ? $latitude : '');
    $longitude   = (($longitude = trim ($this->input_post ('longitude'))) ? $longitude : '');
    $altitude    = (($altitude = trim ($this->input_post ('altitude'))) ? $altitude : '');
    $name        = $this->input_post ('name', true);

    if (!($description && $name))
      return $this->_error ('填寫資訊有少！');

    if (!($user_id && ($user = User::find_by_id ($user_id, array ('select' => 'id')))))
      return $this->_error ('User ID 錯誤！');

    if (!verifyCreateOrm ($picture = Picture::create (array (
        'user_id'     => $user->id,
        'description' => description ($description),
        'name'        => '',
        'gradient'    => 1,
        'latitude'    => $latitude,
        'longitude'   => $longitude,
        'altitude'    => $altitude,
        'color_red'   => '',
        'color_green' => '',
        'color_blue'  => ''
      ))))
      return $this->_error ('新增失敗！');

    if (!$picture->name->put ($name) && ($picture->delete () || true))
      return $this->_error ('新增失敗，上傳圖片失敗！');

    $picture->update_gradient ();

    delay_job ('main', 'picture', array (
        'id' => $picture->id
      ));

    return $this->output_json (array (
      'status' => true,
      'picture' => $this->_picture_format ($picture)
    ));
  }

  public function register () {
    if ($message = $this->_method ('POST'))
      return $this->_error ($message);

    $account  = trim ($this->input_post ('account'));
    $password = trim ($this->input_post ('password'));
    $name     = trim ($this->input_post ('name'));
    $avatar   = $this->input_post ('avatar', true);

    if (!($account && $password && $name && $avatar))
      return $this->_error ('填寫資訊有少！');

    if (User::find_by_account ($account))
      return $this->_error ('帳號已經有人使用！');

    $params = array (
        'account'  => $account,
        'password' => password ($password),
        'name'     => $name,
        'avatar'   => '',
        'color_red'   => '',
        'color_green' => '',
        'color_blue'  => ''
      );

    if (!verifyCreateOrm ($user = User::create ($params)))
      return $this->_error ('新增失敗！');
    
    if (!$user->avatar->put ($avatar) && ($user->delete () || true))
      return $this->_error ('新增失敗，上傳圖片失敗！');

    delay_job ('main', 'user', array (
        'id' => $user->id
      ));

    return $this->output_json (array (
      'status' => true,
      'user' => $this->_user_format ($user)
    ));
  }

  public function login () {
    if ($message = $this->_method ('POST'))
      return $this->_error ($message);

    $account  = trim ($this->input_post ('account'));
    $password = trim ($this->input_post ('password'));

    if (!($user = User::find ('one', array ('conditions' => array ('account = ? AND password = ?', $account, password ($password))))))
      return $this->_error ('找不到使用者！');

    return $this->output_json (array (
      'status' => true,
      'user' => $this->_user_format ($user)
    ));  
  }
}
