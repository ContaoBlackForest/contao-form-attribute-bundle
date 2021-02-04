<?php

/**
 * This file is part of contaoblackforest/contao-form-attribute-bundle.
 *
 * (c) 2014-2019 The Contao Blackforest team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contaoblackforest/contao-form-attribute-bundle
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2019 The Contao Blackforest team.
 * @license    https://github.com/contaoblackforest/contao-form-attribute-bundle/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace BlackForest\Contao\Form\Attribute\Form\Field;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Form;
use Contao\StringUtil;
use Contao\Widget;

/**
 * This has common methods, for parse the form field attribute.
 */
abstract class AbstractFormField
{
    /**
     * The string util.
     *
     * @var Adapter|StringUtil
     */
    protected $stringUtil;

    /**
     * The constructor.
     *
     * @param Adapter $stringUtil The string util.
     */
    public function __construct(Adapter $stringUtil)
    {
        $this->stringUtil = $stringUtil;
    }

    /**
     * Invoke that.
     *
     * @param Widget $widget   The widget.
     * @param string $formId   The form id.
     * @param array  $formData The form data.
     * @param Form   $form     The form.
     *
     * @return Widget
     */
    abstract public function __invoke(Widget $widget, string $formId, array $formData, Form $form): Widget;

    /**
     * Parse the attribute, for the form field.
     *
     * @param Widget $widget The widget.
     *
     * @return void
     */
    protected function parseFormFieldAttribute(Widget $widget): void
    {
        $widget->formFieldAttribute =
            $widget->formFieldAttribute ? ' ' . $this->decodeEntities($widget->formFieldAttribute) : '';
    }
    /**
     * Parse the attribute, for the form field label.
     *
     * @param Widget $widget The widget.
     *
     * @return void
     */
    protected function parseLabelAttribute(Widget $widget): void
    {
        $widget->labelAttribute =
            $widget->labelAttribute ? ' ' . $this->decodeEntities($widget->labelAttribute) : '';
    }

    /**
     * Parse the attribute for each option.
     *
     * @param Widget $widget The widget.
     *
     * @return void
     */
    protected function parseOptionAttribute(Widget $widget): void
    {
        if (empty($options = $widget->options)) {
            return;
        }

        foreach ($options as $key => $option) {
            if (!isset($option['labelAttribute'], $option['formFieldAttribute'])) {
                //FIXME handle group options.
                continue;
            }

            $options[$key]['labelAttribute']     =
                $option['labelAttribute'] ? ' ' . $this->decodeEntities($option['labelAttribute']) : '';
            $options[$key]['formFieldAttribute'] =
                $option['formFieldAttribute'] ? ' ' . $this->decodeEntities($option['formFieldAttribute']) : '';
        }

        $widget->options = $options;
    }

    /**
     * Decode all entities.
     *
     * @param string $string The string to decode.
     *
     * @return string
     */
    protected function decodeEntities(string $string): string
    {
        return $this->stringUtil->decodeEntities($string);
    }

    /**
     * Want for invoke.
     *
     * @param Widget $widget The widget.
     *
     * @return bool
     */
    abstract protected function wantForInvoke(Widget $widget): bool;
}
