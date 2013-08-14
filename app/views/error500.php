<?php
$APP->setLayout('page');
HTML::H(1, 'Se ha producido un error inesperado!', array('style' => 'text-align: center; margin: 30px 0;'));

if (!is_null($APP->vars->error_description)) {
    HTML::Tag('p', HTML::ToHtml($APP->vars->error_description));
}

if (ApplicationEnvIsDevelopment) {
    HTML::H(2, 'Error:');
    echo '<pre>';
    debug_print_backtrace();
    echo '</pre>';
}