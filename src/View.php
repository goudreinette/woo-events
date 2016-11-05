<?php namespace WooEvents;

class View
{
    function __construct($path = 'templates')
    {
        $this->path = plugin_dir_url(__DIR__) . '/' . $path;
    }

    function render($template, $assigns)
    {
        foreach ($assigns as $key => $value) {
            ${$key} = $value;
        }

        return require "$this->path/$template";
    }
}