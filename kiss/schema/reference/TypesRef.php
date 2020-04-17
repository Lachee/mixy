<?php
namespace kiss\schema\reference;

use xve\configuration\Configurator;
use kiss\schema\DynamicRefProperty;
use kiss\schema\EnumProperty;

class TypesRef extends DynamicRefProperty {

    public function __construct($options = []) {
        $function = function ($dyn, $options) {
            assert(isset($options['xve']), 'has configured xve');
            if (isset($options['xve']) && $options['xve'] instanceof Configurator) {
                $types = $options['xve']->getTypes();
                $keys = array_merge(array_keys($types), $options['additional_types'] ?? []);
                return new EnumProperty(null, $keys, null, [
                    'options' => [
                        'selectize' => [
                            'create' => true,
                            'sortField' => 'text'
                        ]
                    ]
                ]);
            }
            
            return null;
        };
        
        parent::__construct('types', $function, $options);
    }


}