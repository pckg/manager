<?php namespace Pckg\Manager;

class Page
{

    public function isHomepage()
    {
        return in_array(router()->getUri(), ['/', '/dev.php', '/dev.php/']);
    }

}