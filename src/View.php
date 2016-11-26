<?php namespace WooEvents;

class View
{
    function __construct($assetsDirectory)
    {
        $this->assetsDirectory     = $assetsDirectory;
        $this->templateDirectory   = $assetsDirectory . 'templates/';
        $this->javascriptDirectory = $assetsDirectory . 'javascript/';
        $this->stylesheetDirectory = $assetsDirectory . 'stylesheets/';
        $this->mustache            = new \Mustache_Engine([
            'loader' => new \Mustache_Loader_FilesystemLoader($this->templateDirectory)
        ]);
    }

    function renderString($template, $assigns)
    {
        return $this->mustache->render($template, $assigns);
    }

    function render($template, $assigns)
    {
        echo $this->renderString($template, $assigns);
        return $this;
    }

    function enqueueStyle($stylesheet)
    {
        wp_enqueue_style($stylesheet, $this->stylesheetDirectory . $stylesheet . '.css');
        return $this;
    }

    function enqueueScript($script, $assigns = null)
    {
        /**
         * Avoid conflicts
         */
        $scriptHash = $script . random_int(0, 999);

        wp_enqueue_script($scriptHash, $this->javascriptDirectory . $script . '.js');

        if (isset($assigns)) {
            wp_localize_script($scriptHash, 'assigns', $assigns);
        }

        return $this;
    }
}