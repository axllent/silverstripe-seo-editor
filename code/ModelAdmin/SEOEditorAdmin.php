<?php

/**
 * Class SEOEditorAdmin
 */
class SEOEditorAdmin extends ModelAdmin
{
    /**
     * @config
     */
    public static $meta_title_min_length = 20;

    public static $meta_title_max_length = 70;

    public static $meta_description_min_length = 100;

    public static $meta_description_max_length = 200;

    private static $menu_title = 'SEO Editor';

    private static $url_segment = 'seo-editor';

    private static $menu_icon = 'silverstripe-seo-editor/images/seo-editor-icon.png';

    private static $managed_models = array(
        'SiteTree'
    );
    /**
     * @var array
     */
    private static $model_importers = array(
        'SiteTree' => 'SEOEditorCSVLoader'
    );
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'ImportForm'
    );

    /**
     * @return SS_HTTPResponse|string|void
     */
    public function init()
    {
        parent::init();
        Requirements::css('silverstripe-seo-editor/css/seo-editor.css');
        Requirements::javascript('silverstripe-seo-editor/javascript/seo-editor.js');
    }

    /**
     * @return SearchContext
     */
    public function getSearchContext()
    {
        $context = parent::getSearchContext();

        $fields = FieldList::create(
            TextField::create('SearchTitle', 'Page Name or Meta Title'),
            TextField::create('MetaDescription', 'Meta Description'),
            CheckboxField::create('DuplicatesOnly', 'Duplicates Only'),
            CheckboxField::create('RemoveEmptyMetaDescriptions', 'Remove Empty Meta Descriptions')
        );

        $context->setFields($fields);
        $filters = array(
            'MetaDescription' => new PartialMatchFilter('MetaDescription')
        );

        $context->setFilters($filters);

        // Namespace fields, for easier detection if a search is present
        foreach ($context->getFields() as $field) {
            $field->setName(sprintf('q[%s]', $field->getName()));
        }
        foreach ($context->getFilters() as $filter) {
            $filter->setFullName(sprintf('q[%s]', $filter->getFullName()));
        }

        return $context;
    }

    /**
     * @param null $id
     * @param null $fields
     * @return mixed
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $grid = $form->Fields()->dataFieldByName('SiteTree');
        if ($grid) {
            $config = $grid->getConfig();
            $config->removeComponentsByType('GridFieldAddNewButton');
            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldEditButton');
            $config->removeComponentsByType('GridFieldExportButton');
            $config->removeComponentsByType('GridFieldDeleteAction');

            $config->getComponentByType('GridFieldDataColumns')->setDisplayFields(
                array(
                    'ID' => 'ID',
                    'SEOEditorMenuTitle' => 'Page',
                )
            );

            $config->addComponent(
                new GridFieldExportButton(
                    'before',
                    array(
                        'ID' => 'ID',
                        'Title' => 'Title',
                        'MetaDescription' => 'MetaDescription'
                    )
                )
            );

            $config->addComponent(new SEOEditorMetaTitleColumn());
            $config->addComponent(new SEOEditorMetaDescriptionColumn());
        }

        return $form;
    }

    /**
     * @return Form
     */
    public function ImportForm()
    {
        $form = parent::ImportForm();
        $modelName = $this->modelClass;

        if ($form) {
            $form->Fields()->removeByName("SpecFor{$modelName}");
            $form->Fields()->removeByName('EmptyBeforeImport');
        }

        return $form;
    }

    /**
     * Get the list for the GridField
     *
     * @return SS_List
     */
    public function getList()
    {
        $list = parent::getList();
        $params = $this->request->requestVar('q');

        if (isset($params['RemoveEmptyMetaDescriptions']) && $params['RemoveEmptyMetaDescriptions']) {
            $list = $this->removeEmptyAttributes($list, 'MetaDescription');
        }

        if (isset($params['DuplicatesOnly']) && $params['DuplicatesOnly']) {
            $list = $this->filterDuplicates($list);
        }

        if (!empty($params['SearchTitle'])) {
            $list = $list->filterAny([
                'MenuTitle:PartialMatch' => $params['SearchTitle'],
                'Title:PartialMatch' => $params['SearchTitle'],
            ]);
        }

        $list = $list->exclude('ClassName', $this->config()->ignore_page_types); // remove error pages etc

        $list = $list->sort('ID');

        return $list;
    }

    /**
     * Return only duplicate items
     *
     * @param SS_List $list
     * @return SS_List
     */
    private function filterDuplicates(SS_List $list)
    {
        $duplicateList = new ArrayList();

        $duplicate_ids = $this->findDuplicates($list, 'Title');
        foreach ($list as $item) {
            if (in_array($item->ID, $duplicate_ids)) {
                $duplicateList->push($item);
            }
        }

        $duplicate_ids = $this->findDuplicates($list, 'MetaDescription');
        foreach ($list as $item) {
            if (in_array($item->ID, $duplicate_ids) && !$duplicateList->byID($item->ID)) {
                $duplicateList->push($item);
            }
        }

        return $duplicateList;
    }

    /**
     * Find duplicate attributes within a list
     *
     * @param SS_List $list
     * @param string $type
     * @return SS_List
     */
    private function findDuplicates(SS_List $list, $type)
    {
        $pageAttributes = $list->map('ID', $type)->toArray();

        $duplicates = array_values(
            array_unique(
                array_diff_key($pageAttributes, array_unique($pageAttributes))
            )
        );

        $duplicateAttributes = [];

        foreach ($pageAttributes as $id => $val) {
            if (in_array($val, $duplicates)) {
                array_push($duplicateAttributes, $id);
            }
        }

        if (!count($duplicateAttributes)) {
            return [];
        }

        return $duplicateAttributes;
    }

    /**
     * Remove pages with empty attributes
     *
     * @param SS_List $list
     * @param string $type
     * @return SS_List
     */
    private function removeEmptyAttributes(SS_List $list, $type)
    {
        $pageAttributes = $list->map('ID', $type)->toArray();

        $emptyAttributess = array_map(function ($value) {
            return $value == '';
        }, $pageAttributes);

        if (!count($emptyAttributess)) {
            return $list;
        }

        return $list->filter(
            array(
                'ID:not' => array_keys(
                    array_filter(
                        $emptyAttributess,
                        function ($value) {
                            return $value == 1;
                        }
                    )
                )
            )
        );
    }
}
