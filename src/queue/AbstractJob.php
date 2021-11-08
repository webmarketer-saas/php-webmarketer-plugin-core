<?php

namespace WebmarketerPluginCore\Queue;

use Webmarketer\WebmarketerSdk;

abstract class AbstractJob
{

    public function getName()
    {
        return 'Untitled';
    }

    /**
     * @param $sdk WebmarketerSdk
     * @return boolean
     */
    public abstract function handle($sdk);

    protected function skip($message = '')
    {
        throw new JobSkipException($message);
    }

}