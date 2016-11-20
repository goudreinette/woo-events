<?php namespace WooEvents;

class View
{
    function __construct($assetsDirectory)
    {
        $this->assetsDirectory     = $assetsDirectory;
        $this->templateDirectory   = $assetsDirectory . '/templates/';
        $this->javascriptDirectory = $assetsDirectory . '/javascript/';
        $this->stylesheetDirectory = $assetsDirectory . '/stylesheets/';
        $this->mustache            = new \Mustache_Engine([
            'loader' => new \Mustache_Loader_FilesystemLoader($assetsDirectory . '/templates')
        ]);
    }

    function render($template, $assigns)
    {
        return $this->mustache->render($template, $assigns);
    }

    function echo ($template, $assigns)
    {
        echo $this->render($template, $assigns);
        return $this;
    }

    function enqueueStyle($stylesheet)
    {
        wp_enqueue_style($stylesheet, $this->stylesheetDirectory . $stylesheet . '.css');
        return $this;
    }

    function enqueueScript($script, $assigns = null)
    {
        wp_enqueue_script($script, $this->javascriptDirectory . $script . '.js');

        if (isset($assigns)) {
            wp_localize_script($script, 'assigns', $assigns);
        }

        return $this;
    }
}