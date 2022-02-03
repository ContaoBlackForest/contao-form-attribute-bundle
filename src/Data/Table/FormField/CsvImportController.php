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
 * @author     Leo Feyer <github@contao.org>
 * @copyright  2014-2019 The Contao Blackforest team.
 * @license    https://github.com/contaoblackforest/contao-form-attribute-bundle/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace BlackForest\Contao\Form\Attribute\Data\Table\FormField;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\FileUpload;
use Contao\Message;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Webmozart\PathUtil\Path;

/**
 * The csv import controller.
 */
final class CsvImportController
{
    public const SEPARATOR_COMMA     = 'comma';
    public const SEPARATOR_LINEBREAK = 'linebreak';
    public const SEPARATOR_SEMICOLON = 'semicolon';
    public const SEPARATOR_TABULATOR = 'tabulator';

    /**
     * The framework.
     *
     * @var ContaoFramework
     */
    private $framework;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The project directory.
     *
     * @var string
     */
    private $projectDir;

    /**
     * The constructor.
     *
     * @param ContaoFramework     $framework    The framework.
     * @param Connection          $connection   The database connection.
     * @param RequestStack        $requestStack The request stack.
     * @param TranslatorInterface $translator   The translator.
     * @param string              $projectDir   The project directory.
     */
    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        string $projectDir
    ) {
        $this->framework    = $framework;
        $this->connection   = $connection;
        $this->requestStack = $requestStack;
        $this->translator   = $translator;
        $this->projectDir   = $projectDir;
    }

    /**
     * The import option wizard action.
     *
     * @param DataContainer $dc The data container.
     *
     * @return Response
     */
    public function importOptionWizardAction(DataContainer $dc): Response
    {
        return $this->importFromTemplate(
            static function (array $data, array $row): array {
                $data[] = [
                    'value'              => $row[0],
                    'label'              => $row[1],
                    'formFieldAttribute' => $row[2] ?? '',
                    'labelAttribute'     => $row[3] ?? '',
                    'default'            => !empty($row[4]) ? 1 : '',
                    'group'              => !empty($row[5]) ? 1 : ''
                ];

                return $data;
            },
            $dc->table,
            'options',
            (int) $dc->id,
            $this->translator->trans('MSC.ow_import.0', [], 'contao_default')
        );
    }

    /**
     * Import from the template.
     *
     * @param callable    $callback       The callback.
     * @param string      $table          The table.
     * @param string      $field          The field,
     * @param int         $id             The id.
     * @param string|null $submitLabel    The submitLabel.
     * @param bool        $allowLinebreak The allow linebreak flag.
     *
     * @return Response
     *
     * @throws InternalServerErrorException Throws if, no request object given.
     */
    private function importFromTemplate(
        callable $callback,
        string $table,
        string $field,
        int $id,
        string $submitLabel = null,
        bool $allowLinebreak = false
    ): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new InternalServerErrorException('No request object given.');
        }

        $this->framework->initialize();

        /** @var FileUpload $uploader */
        $uploader = $this->framework->createInstance(FileUpload::class);
        $template = $this->prepareTemplate($request, $uploader, $allowLinebreak);

        if (null !== $submitLabel) {
            $template->submitLabel = $submitLabel;
        }

        if ($request->request->get('FORM_SUBMIT') === $this->getFormId($request)) {
            try {
                $data = $this->fetchData($uploader, $request->request->get('separator', ''), $callback);
            } catch (\RuntimeException $e) {
                /** @var Message $message */
                $message = $this->framework->getAdapter(Message::class);
                $message->addError($e->getMessage());

                return new RedirectResponse($request->getUri());
            }

            $this->connection->update(
                $table,
                [$field => \serialize($data)],
                ['id' => $id]
            );

            return new RedirectResponse($this->getBackUrl($request));
        }

        return new Response($template->parse());
    }

    /**
     * Prepare the template.
     *
     * @param Request    $request        The request.
     * @param FileUpload $uploader       The uploader.
     * @param bool       $allowLinebreak The allow linebreak flag.
     *
     * @return BackendTemplate
     */
    private function prepareTemplate(
        Request $request,
        FileUpload $uploader,
        bool $allowLinebreak = false
    ): BackendTemplate
    {
        $template = new BackendTemplate('be_csv_import');

        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $template->formId          = $this->getFormId($request);
        $template->backUrl         = $this->getBackUrl($request);
        $template->fileMaxSize     = $config->get('maxFileSize');
        $template->uploader        = $uploader->generateMarkup();
        $template->separators      = $this->getSeparators($allowLinebreak);
        $template->submitLabel     = $this->translator->trans('MSC.apply', [], 'contao_default');
        $template->backBT          = $this->translator->trans('MSC.backBT', [], 'contao_default');
        $template->backBTTitle     = $this->translator->trans('MSC.backBTTitle', [], 'contao_default');
        $template->separatorLabel  = $this->translator->trans('MSC.separator.0', [], 'contao_default');
        $template->separatorHelp   = $this->translator->trans('MSC.separator.1', [], 'contao_default');
        $template->sourceLabel     = $this->translator->trans('MSC.source.0', [], 'contao_default');
        $template->sourceLabelHelp = $this->translator->trans('MSC.source.1', [], 'contao_default');

        return $template;
    }

    /**
     * Returns an array of data from the imported CSV files.
     *
     * @param FileUpload $uploader  The uploader.
     * @param string     $separator The separator.
     * @param callable   $callback  The callback.
     *
     * @return array<string>
     */
    private function fetchData(FileUpload $uploader, string $separator, callable $callback): array
    {
        $data      = [];
        $files     = $this->getFiles($uploader);
        $delimiter = $this->getDelimiter($separator);

        foreach ($files as $file) {
            $fp = \fopen($file, 'r');

            while (false !== ($row = \fgetcsv($fp, 0, $delimiter))) {
                $data = $callback($data, $row);
            }
        }

        return $data;
    }

    /**
     * Get the form id.
     *
     * @param Request $request The request.
     *
     * @return string
     */
    private function getFormId(Request $request): string
    {
        return 'tl_csv_import_' . $request->query->get('key');
    }

    /**
     * Get the back url.
     *
     * @param Request $request The request.
     *
     * @return string
     */
    private function getBackUrl(Request $request): string
    {
        return \str_replace('&key=' . $request->query->get('key'), '', $request->getRequestUri());
    }

    /**
     * Get the separators.
     *
     * @param bool $allowLinebreak The allow line break flag.
     *
     * @return array<string,array<string,string>>
     */
    private function getSeparators(bool $allowLinebreak = false): array
    {
        $separators = [
            self::SEPARATOR_COMMA     => [
                'delimiter' => ',',
                'value'     => self::SEPARATOR_COMMA,
                'label'     => $this->translator->trans('MSC.comma', [], 'contao_default'),
            ],
            self::SEPARATOR_SEMICOLON => [
                'delimiter' => ';',
                'value'     => self::SEPARATOR_SEMICOLON,
                'label'     => $this->translator->trans('MSC.semicolon', [], 'contao_default'),
            ],
            self::SEPARATOR_TABULATOR => [
                'delimiter' => "\t",
                'value'     => self::SEPARATOR_TABULATOR,
                'label'     => $this->translator->trans('MSC.tabulator', [], 'contao_default'),
            ],
        ];

        if ($allowLinebreak) {
            $separators[self::SEPARATOR_LINEBREAK] = [
                'delimiter' => "\n",
                'value'     => self::SEPARATOR_LINEBREAK,
                'label'     => $this->translator->trans('MSC.linebreak', [], 'contao_default'),
            ];
        }

        return $separators;
    }

    /**
     * Get the delimiter.
     *
     * @param string $separator The separator.
     *
     * @return string
     *
     * @throws \RuntimeException Throws, if no separator isset.
     */
    private function getDelimiter(string $separator): string
    {
        $separators = $this->getSeparators(true);

        if (!isset($separators[$separator])) {
            throw new \RuntimeException($this->translator->trans('MSC.separator.1', [], 'contao_default'));
        }

        return $separators[$separator]['delimiter'];
    }

    /**
     * Returns the uploaded files from a FileUpload instance.
     *
     * @param FileUpload $uploader The uploader.
     *
     * @return array<string>
     *
     * @throws \RuntimeException Throws, if no files found.
     * @throws \RuntimeException Throws, if the file extension not support.
     */
    private function getFiles(FileUpload $uploader): array
    {
        $files = $uploader->uploadTo('system/tmp');

        if (\count($files) < 1) {
            throw new \RuntimeException($this->translator->trans('ERR.all_fields', [], 'contao_default'));
        }

        foreach ($files as &$file) {
            $extension = Path::getExtension($file, true);

            if ('csv' !== $extension) {
                throw new \RuntimeException(
                    \sprintf($this->translator->trans('ERR.filetype', [], 'contao_default'), $extension)
                );
            }

            $file = Path::join($this->projectDir, $file);
        }

        return $files;
    }
}
