<?php

namespace Sensio\Bundle\GeneratorBundle\Translator;

class UnderscoreLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        $label = str_replace('.', '_', $label);

        return sprintf('%s.%s_%s', $context, $type, strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label)));
    }
}
