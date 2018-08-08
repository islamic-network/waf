<?php

namespace IslamicNetwork\Waf\Model;


use IslamicNetwork\Memcached\Cacher;

class RateLimit
{
    public function __construct(Cacher $memcached, $name, $limit, $time)
    {
        $this->memcached = $memcached;
        $this->name = 'WAF_RL_' . str_replace(' ', '_', $name);
        $this->limit = $limit;
        $this->time = $time;
        $this->load();
    }

    public function load()
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

    public function allowed()
    {
        $timeLimit = $this->record['start'] + $this->time;

        if (time() < $timeLimit && $this->record['hits'] <= $this->limit) {
            return true;
        }

        return false;
    }

    public function expired()
    {
        $timeLimit = $this->record['start'] + $this->time;

        if (time() > $timeLimit) {
            return true;
        }

        return false;
    }

    public function initialize()
    {
        $this->record = ['start' => time(), 'hits' => 0];

        return $this->update();

    }

    public function exists()
    {
        return $this->memcached->check($this->name);
    }

    public function update()
    {
        $this->record['hits'] += 1;

        return $this->memcached->set($this->name, $this->record);
    }

}