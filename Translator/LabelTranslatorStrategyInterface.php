<?php

namespace Sensio\Bundle\GeneratorBundle\Translator;

interface LabelTranslatorStrategyInterface
{
    /**
     * @param string $label
     * @param string $context
     * @param string $type
     *
     * @return string
     */
    public function getLabel($label, $context = '', $type = '');
}
