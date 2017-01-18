<?php

/**
 * Class SEOEditorCSVLoader
 */
class SEOEditorCSVLoader extends CsvBulkLoader
{

    /**
     * @var array
     */
    public $duplicateChecks = array(
        'ID' => 'ID',
    );

    /**
     * Update the columns needed when importing from CSV
     *
     * @param array $record
     * @param array $columnMap
     * @param BulkLoader_Result $results
     * @param bool $preview
     * @return bool|int
     */
    public function processRecord($record, $columnMap, &$results, $preview = false)
    {
        $page = $this->findExistingObject($record, $columnMap);

        if (!$page || !$page->exists()) {
            return false;
        }

        foreach ($record as $fieldName => $val) {
            if ($fieldName == 'Title' || $fieldName == 'MetaDescription') {

                $val = trim(preg_replace('/\s+/', ' ', $val));
                $sqlValue = Convert::raw2sql($val);

                /* Make sure the MenuTitle remains unchanged! */
                if ($fieldName == 'Title') {
                    $query = DB::query("SELECT MenuTitle, Title FROM SiteTree WHERE ID = {$page->ID}");
                    foreach ($query as $row) {
                        $menuTitle = '\'' . Convert::raw2sql($row['MenuTitle']) . '\'';
                        if (is_null($row['MenuTitle'])) {
                            $menuTitle = '\'' . Convert::raw2sql($row['Title']) . '\'';
                        }
                        if ($menuTitle == '\'' . $sqlValue . '\'') { // set back to NULL
                            $menuTitle = 'NULL';
                        }

                        DB::query("UPDATE SiteTree SET MenuTitle = " . $menuTitle . " WHERE ID = {$page->ID}");
                        if ($page->isPublished()) {
                            DB::query("UPDATE SiteTree_Live SET MenuTitle = " . $menuTitle . " WHERE ID = {$page->ID}");
                        }
                    }
                }

                DB::query("UPDATE SiteTree SET {$fieldName} = '{$sqlValue}' WHERE ID = {$page->ID}");
                if ($page->isPublished()) {
                    DB::query("UPDATE SiteTree_Live SET {$fieldName} = '{$sqlValue}' WHERE ID = {$page->ID}");
                }
            }
        }

        return $page->ID;
    }

}
