<?php

namespace Pckg\Manager\Provider;

class Pckg
{
    public function chain($chain)
    {
        if (!is_array($chain)) {
            $chain = [$chain];
        }

        return chain($chain);
    }
}
