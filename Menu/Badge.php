<?php

namespace Alpixel\Bundle\AdminMenuBundle\Menu;

class Badge
{
    protected $count = 0;

    public function __construct($service, $repositoryName, $repositoryMethod, $parameters = [])
    {
        $this->count = 0;
        $this->doCount($service, $repositoryName, $repositoryMethod, $parameters);
    }

    protected function doCount($service, $repositoryName, $repositoryMethod, $parameters)
    {
        $repository = $service->getRepository($repositoryName);
        $count = call_user_func([$repository, $repositoryMethod], $parameters);
        if (!is_numeric($count)) {
            throw new \Exception('Badge result is not a valid numeric value');
        }
        $this->count = $count;
    }

    public function getCount()
    {
        return $this->count;
    }
}
