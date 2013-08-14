<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php HTML::PageTitle(); ?></title>
        <link rel="shortcut icon" href="<?php $APP->resourceImg('favicon.ico'); ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <meta name="description" content="<?php echo HtmlMetatagDescription . $APP->page_title; ?>" />
        <meta name="keywords" content="<?php echo $APP->getKeywords(); ?>" />
        <meta name="author" content="Eduardo D. Cuomo" />
        <meta name="copyright" content="Eduardo D. Cuomo" />
        <meta name="robots" content="all" />
        <meta name="rating" content="General" />
        <meta name="revisit-after" content="31 days" />
        <meta name="DC.Title" content="<?php echo $APP->page_title; ?>" />
        <meta name="DC.Language" scheme="RFC1766" content="ES" />
        <meta name="DC.Coverage.PlaceName" content="Global" />
        <meta name="DC.Subject" content="<?php echo $APP->page_title; ?>" />

        <meta name="viewport" content="width=device-width; initial-scale=1; maximum-scale=1" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <link rel="apple-touch-icon" sizes="114x114" href="<?php $APP->resourceImg('icons/icon_home.png'); ?>" />
        <?php
        $APP->style('main.xphp.css')
            ->js('jquery.min.js')
            ->js('jquery.flexslider.js')
            ->js('main.js')
            ->renderStyles();
        ?>
    </head>
    <body class="home">
        <div id="container">
            <?php $APP->renderContent(); ?>
        </div>
        <?php $APP->renderJs(); ?>
    </body>
</html>