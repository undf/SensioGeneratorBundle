<?php

namespace Sensio\Bundle\GeneratorBundle\Translator;

class BCLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        if ($context == 'breadcrumb') {
            return sprintf('%s.%s_%s', $context, $type, strtolower($label));
        }

        return ucfirst(strtolower($label));
    }
}
