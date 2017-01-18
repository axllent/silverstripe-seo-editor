<?php

/**
 * An easy-to-read "Page summary" for modeladmin overview
 */
class SEOEditorMenuTitleExt extends SiteTreeExtension
{

    public function getSEOEditorMenuTitle()
    {
        return DBField::create_field(
            'HTMLVarchar',
            '<strong>' . convert::raw2xml($this->owner->MenuTitle) . '</strong><br />'.
            'URL: <a href="' . $this->owner->Link() . '" target="_blank">' . convert::raw2xml($this->owner->Link()) . '</a>'
        );
    }

}
