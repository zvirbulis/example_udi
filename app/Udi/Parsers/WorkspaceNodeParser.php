<?php


namespace App\Udi\Parsers;

use App\Udi\Nodes\BaseNode;
use App\Udi\Parsers\Workspace\FormNodeParser;
use App\Udi\Nodes\WorkspaceNode;
use App\Udi\Parsers\Workspace\ListNodeParser;

class WorkspaceNodeParser extends BaseNodeParser
{
    protected $parsers = [
        'list' => ListNodeParser::class,
        'forms.self.grid' => FormNodeParser::class,
        'forms.parent.grid' => FormNodeParser::class
    ];

    public function __construct($workspace)
    {
        parent::__construct(new WorkspaceNode($workspace));
    }

    public function parse($data): BaseNode
    {
        if($this->isCustomWorkspace($data)){
            unset($this->parsers['list'], $this->parsers['forms.parent.grid']);
        }
        $this->extendsFilterFields($data);
        /**
         * @var WorkspaceNode $workspace
         */
        $workspace = parent::parse($data);

        return $workspace;
    }

    private function extendsFilterFields(&$data)
    {
        if (
            !isset($data['list']['filter']) ||
            !isset($data['forms']['self'])
        ) {
            return;
        }

        $filter = &$data['list']['filter'];
        if (isset($filter['fields'])) {
            $form = $data['forms']['self'];
            if (isset($form['grid']['fields'])) {
                $formFields = $form['grid']['fields'];
                foreach ($filter['fields'] as $fieldCode => &$fieldData) {
                    if (!isset($fieldData['value']) || !$fieldData['value']) {
                        $fieldData['value'] = null;
                    }
                    if (isset($formFields[$fieldCode])) {
                        $this->removeFilterFields($formFields[$fieldCode], [
                            'validation', 'required', 'errors'
                        ]);
                        $fieldData = array_replace_recursive($formFields[$fieldCode], $fieldData);
                    }
                }
                unset($fieldData);
            }
        }
    }

    private function removeFilterFields(&$formField, array $fields)
    {
        foreach ($fields as $fieldCode) {
            if (isset($formField[$fieldCode])) {
                unset($formField[$fieldCode]);
            }
        }
    }

    private function isCustomWorkspace($data)
    {
        return isset($data['custom']) && $data['custom'] === true;
    }
}
