<?php

namespace Rennypoz\Eavquent;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rennypoz\Eavquent\Skeleton\SkeletonClass
 */
class EavquentFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eavquent';
    }
}
