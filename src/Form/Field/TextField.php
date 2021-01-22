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

use Contao\Form;
use Contao\FormRadioButton;
use Contao\FormTextField;
use Contao\Widget;

/**
 * This parse the attribute, for the text frontend form field.
 */
final class TextField extends AbstractFormField
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(Widget $widget, string $formId, array $formData, Form $form): Widget
    {
        if (false === $this->wantForInvoke($widget)) {
            return $widget;
        }

        $this->parseFormFieldAttribute($widget);
        $this->parseLabelAttribute($widget);

        return $widget;
    }

    /**
     * {@inheritDoc}
     */
    protected function wantForInvoke(Widget $widget): bool
    {
        return $widget instanceof FormTextField;
    }
}
