<?php
/**
* PHP-CS-Fixer Configuration.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\CS;

use SignpostMarv\CS\ConfigUsedWithStaticAnalysis as Base;

class ConfigUsedWithStaticAnalysis extends Base
{
    protected static function RuntimeResolveRules() : array
    {
        $rules = parent::RuntimeResolveRules();

        $rules['phpdoc_no_alias_tag'] = [
            'type' => 'var',
            'link' => 'see'
        ];

        return $rules;
    }
}
