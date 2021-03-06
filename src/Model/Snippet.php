<?php

namespace SilverStripe\TagManager\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\TagManager\Admin\ParamExpander;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;

/**
 * Represents one snippet added to the site with is params configured
 */
class Snippet extends DataObject
{

    use ParamExpander;

    private static $db = [
        "SnippetClass" => "Varchar(255)",
        "SnippetParams" => "Text",
        "Active" => "Enum('on,off,partial', 'on')",
    ];

    private static $has_many = [
        'Pages' => SnippetPage::class,
    ];

    private static $summary_fields = [
        "SnippetSummary",
        "ActiveLabel",
    ];

    private static $active_labels = [
        'on' => 'Enabled',
        'off' => 'Disabled',
    ];

    public function getTitle()
    {
        return $this->getSnippetProvider()->getTitle();
    }

    public function getSnippetSummary()
    {
        return $this->getSnippetProvider()->getSummary(json_decode($this->SnippetParams, true));
    }

    public function getActiveLabel() {
        return self::$active_labels[$this->Active];
    }

    /**
     * Return the snippet provider attached to this record
     */
    protected function getSnippetProvider()
    {
        if ($this->SnippetClass) {
            return Injector::inst()->get($this->SnippetClass);
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('Active')->setSource(self::$active_labels);

        $providerFields = null;
        if ($provider = $this->getSnippetProvider()) {
            $providerFields = $provider->getParamFields();
        }
        $this->expandParams('SnippetParams', $providerFields, $fields, 'Root.Main');

        return $fields;
    }

    /**
     * Return the snippets generated by the configured provider
     */
    public function getSnippets()
    {
        if ($provider = $this->getSnippetProvider()) {
            $params = (array)json_decode($this->SnippetParams, true);
            return $provider->getSnippets($params);
        }
    }
}
