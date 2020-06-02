<?php

namespace Concrete\Package\Concrete5CommunityStoreTemplates;

use Concrete\Core\Package\Package;

class Controller extends Package
{

    protected $appVersionRequired = '8.5.2';
    protected $pkgVersion = '0.0.1';
    protected $pkgHandle = 'concrete5_community_store_templates';
    protected $pkgName = 'Concrete5 Community Store Templates';
    protected $pkgDescription = 'A package to provide templates for the Community Store blocks.';

    public function on_start()
    {
    }

}
