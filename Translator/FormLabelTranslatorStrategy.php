<?php

namespace Sensio\Bundle\GeneratorBundle\Translator;

class FormLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        return ucfirst(strtolower($label));
    }
}
