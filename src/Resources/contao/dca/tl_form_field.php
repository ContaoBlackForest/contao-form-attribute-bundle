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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// FIXME make supported form field configurable.
$supportedFormFields = [
    'fieldsetfsStart' => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'type'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'text'            => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'textdigit'       => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'textarea'        => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'select'          => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'radio'           => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            //'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'checkbox'        => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'upload'          => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'range'           => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ],
    'submit'          => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'slabel']
        ]
    ],
    'hidden'          => [
        'palette' => [
            'formFieldAttribute' => ['addAfter' => 'name'],
            'labelAttribute'     => ['addAfter' => 'label']
        ]
    ]
];
foreach (array_keys($GLOBALS['TL_DCA']['tl_form_field']['palettes']) as $paletteName) {
    if (true === isset($supportedFormFields[$paletteName]['palette']['formFieldAttribute'])) {
        PaletteManipulator::create()
            ->addField('formFieldAttribute', $supportedFormFields[$paletteName]['palette']['formFieldAttribute']['addAfter'])
            ->applyToPalette($paletteName, 'tl_form_field');
    }

    if (true === isset($supportedFormFields[$paletteName]['palette']['labelAttribute'])) {
        PaletteManipulator::create()
            ->addField('labelAttribute', $supportedFormFields[$paletteName]['palette']['labelAttribute']['addAfter'])
            ->applyToPalette($paletteName, 'tl_form_field');
    }
}

$GLOBALS['TL_DCA']['tl_form_field']['fields']['label']['eval']['tl_class'] .= ' clr';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['options']['inputType']            = 'multiColumnWizard';
$GLOBALS['TL_DCA']['tl_form_field']['fields']['options']['eval']['columnFields'] = [
    'value' => [
        'label'         => &$GLOBALS['TL_LANG']['MSC']['ow_value'],
        'exclude'       => true,
        'inputType'     => 'text',
        'eval'          => ['allowHtml' => true]
    ],
    'label' => [
        'label'         => &$GLOBALS['TL_LANG']['MSC']['ow_label'],
        'exclude'       => true,
        'inputType'     => 'text',
        'eval'          => ['mandatory' => true, 'allowHtml' => true]

    ],
    'formFieldAttribute' => [
        'label'         => &$GLOBALS['TL_LANG']['tl_form_field']['options']['formFieldAttribute'],
        'exclude'       => true,
        'inputType'     => 'text',
    ],
    'labelAttribute' => [
        'label'         => &$GLOBALS['TL_LANG']['tl_form_field']['options']['labelAttribute'],
        'exclude'       => true,
        'inputType'     => 'text',
    ],
    'default' => [
        'label'         => &$GLOBALS['TL_LANG']['MSC']['ow_default'],
        'exclude'       => true,
        'inputType'     => 'checkbox',
    ],
    'group' => [
        'label'         => &$GLOBALS['TL_LANG']['MSC']['ow_group'],
        'exclude'       => true,
        'inputType'     => 'checkbox',
    ]
];
unset(
    $GLOBALS['TL_DCA']['tl_form_field']['fields']['options']['eval']['mandatory'],
    $GLOBALS['TL_DCA']['tl_form_field']['fields']['options']['eval']['allowHtml']
);

$GLOBALS['TL_DCA']['tl_form_field']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_form_field']['fields'],
    [
        'formFieldAttribute' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_form_field']['formFieldAttribute'],
            'exclude'       => true,
            'search'        => true,
            'inputType'     => 'text',
            'eval'          => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'           => ['type' => 'string', 'length' => 255, 'default' => '']
        ),
        'labelAttribute' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_form_field']['labelAttribute'],
            'exclude'       => true,
            'search'        => true,
            'inputType'     => 'text',
            'eval'          => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'           => ['type' => 'string', 'length' => 255, 'default' => '']
        )
    ]
);
