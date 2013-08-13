<?php

namespace Sensio\Bundle\GeneratorBundle\Translator;

class NativeLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        $label = str_replace(array('_', '.'), ' ', $label);
        $label = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label));

        return trim(ucwords(str_replace('_', ' ', $label)));
    }
}
