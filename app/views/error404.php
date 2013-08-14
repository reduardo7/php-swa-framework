<?php
if ($APP->params->context == 'admin') {
    $APP->setLayout('login');
} else {
    $APP->setLayout('page');
}
HTML::H(1, 'P&aacute;gina no encontrada!', array('style' => 'text-align: center; margin: 30px 0;'));