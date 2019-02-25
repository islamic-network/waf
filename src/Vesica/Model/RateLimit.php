<?php

namespace Vesica\Waf\Model;

use Vesica\Waf\Cacher\Memcached;

class RateLimit
{

    public function __construct(Memcached $memcached, $name, $limit, $time)
    {
        $this->memcached = $memcached;
        $this->name = str_replace(' ', '_', $name);
        $this->limit = $limit;
        $this->time = $time;
        $this->load();
    }

    private function load()
    {
        $this->record = $this->memcached->get($this->name);
    }

    /**
     * Should we limit this request?
     * @return bool
     */
    public function isLimited(): bool
    {
        if (!$this->exists() || $this->expired()) {
            $this->initialize();
            // Allow request
            return false;
        }

        if ($this->allowed()) {
            $this->update();
            return false;
        }

        // Not allowed, not new and not expired.
        return true;
    }

    private function allowed(): bool
    {
        $timeLimit = $this->record['start'] + $this->time;

        if (time() < $timeLimit && $this->record['hits'] <= $this->limit) {
            return true;
        }

        return false;
    }

    private function expired(): bool
    {
        $timeLimit = $this->record['start'] + $this->time;

        if (time() > $timeLimit) {
            return true;
        }

        return false;
    }

    private function initialize(): bool
    {
        $this->record = ['start' => time(), 'hits' => 1];

        return $this->update();

    }

    private function exists(): bool
    {
        return $this->memcached->exists($this->name);
    }

    private function update(): bool
    {
        $this->record['hits'] += 1;

        return $this->memcached->set($this->name, $this->record);
    }

}
