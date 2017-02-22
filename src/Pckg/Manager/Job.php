<?php namespace Pckg\Manager;

class Job
{

    protected $jobs = [];

    public function add($job)
    {
        $this->jobs[] = $job;

        return $this;
    }

    public function all()
    {
        return $this->jobs;
    }

}