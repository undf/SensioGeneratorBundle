<?php

namespace Sensio\Bundle\GeneratorBundle\Translator;

class NoopLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        return $label;
    }
}
