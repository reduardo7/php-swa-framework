<?php

class ModelUsuarios extends ModelBase {
    protected $_clazz = __CLASS__;
    const TITLE = 'Usuarios';
    const TABLE = 'usuarios';

    const TIPO_SUPER_ADMIN = 1;
    const TIPO_ADMIN       = 2;
    const TIPO_USUARIO     = 3;

    protected $_columns = array(
        'id'       => 'id',
        'email'    => array('email', 'title' => 'e-Mail'),
        'password' => array('string', 'label' => 'Password'),
        'nombre'   => array('string', 'title' => 'Nombre'),
        'tipo'     => array('title' => 'Tipo', 'enum' => array(
            self::TIPO_SUPER_ADMIN  => 'Super-Admin',
            self::TIPO_ADMIN        => 'Admin',
            self::TIPO_USUARIO      => 'Usuario'
        )),
        'activo' => array('boolean', 'render' => false)
    );

    public function setFormFields(Form $frm) {
        parent::setFormFields($frm);
        if ($this->APP->login->isAdmin()) {
            if (!$this->APP->login->get('id') || ($this->APP->login->get('id') != $this->id)) {
                // Reemplaza el selector de tipo
                $frm->addHidden('tipo', self::TIPO_USUARIO);
                // Remueve la opcion "Super-Admin"
                unset($this->_columns['tipo']['enum'][self::TIPO_SUPER_ADMIN]);
            }
        }
    }

//  public function onBeforeSave() {
//      if ($this->isRecord()) {
//          // Update
//          // TODO: No encrypt if not changed
//          $this->password == $this->encodePassword();
//      } else {
//          // New
//          $this->password = $this->encodePassword();
//      }
//  }

//  /**
//   * Encode setted password.
//   */
//  protected function encodePassword() {
//      $this->password = self::EncodedPassword($this->password);
//  }

//  /**
//   * Encode password.
//   *
//   * @param string $password Password to encode.
//   * @return string Encoded password.
//   */
//  public static function EncodedPassword($password) {
//      return md5(md5(base64_encode(md5($password) . 'pWd')) . $password . base64_encode($password) . strlen($password));
//  }

    public function toString() {
        return "{$this->nombre} ($this->email)";
    }
}