<?php

class ModelUsuarios extends ModelBase {
    const TITLE = 'Users';
    const TABLE = 'users';

    protected $_old_pass = null;

    protected $_columns = array(
        'id'       => 'id',
        'email'    => array('email', 'title' => 'e-Mail'),
        'password' => array('string', 'label' => 'Password')
    );

    public function onBeforeLoad() {
        parent::onBeforeLoad();
        $this->_old_pass = $this->password;
    }

    public function onBeforeSave() {
        if ($this->isRecord()) {
            // Update
            if ($this->_old_pass !== $this->password) {
                $this->password == $this->encodePassword();
            }
        } else {
            // New
            $this->password = $this->encodePassword();
        }
    }

    /**
     * Encode setted password.
     */
    protected function encodePassword() {
        $this->password = self::EncodedPassword($this->password);
    }

    /**
     * Encode password.
     *
     * @param string $password Password to encode.
     * @return string Encoded password.
     */
    public static function EncodedPassword($password) {
        return md5(md5(base64_encode(md5($password) . 'pWd')) . $password . base64_encode($password) . strlen($password));
    }

    public function toString() {
        return $this->email;
    }
}