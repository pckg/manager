<?php

namespace Pckg\Manager\Service;

class Domain
{
    const NAME_SERVERS = ['1.1.1.1', '8.8.8.8', '8.8.4.4'];

    protected $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function getTxtRecords($subdomain = null)
    {
        $values = cache(Domain::class . ':' . sha1($this->domain . '---' . $subdomain . '---' . DNS_TXT), function () use ($subdomain) {
            $ns = static::NAME_SERVERS;
            return dns_get_record($subdomain . $this->domain, DNS_TXT, $ns);
        }, 'app', '60minutes');

        return collect($values)->map(function ($value) {
            return implode($value['entries'] ?? []);
        })->removeEmpty()->values();
    }
}
