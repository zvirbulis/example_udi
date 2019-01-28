<?php


namespace App\Udi\Resources;

use App\Models\Users\User;
use App\Udi\Interfaces\UdiUserInterface;
use App\Udi\SchemaRepositories\AbstractSchemaRepository;
use App\Udi\Schemas\AbstractSchema;

abstract class AbstractResource
{
    protected $name;
    protected $baseUrl;
    protected $schemaRepository;

    private $schemas = [];
    private $filteredByAccess = false;

    public function __construct($resourceName, AbstractSchemaRepository $schemaRepository)
    {
        $this->name = $resourceName;
        $this->schemaRepository = $schemaRepository;
        $this->initBaseUrl();
    }

    protected function initBaseUrl()
    {
        $currentUrl = \Request::url();
        $urlParams = explode('/', $currentUrl);
        $lastParam = array_pop($urlParams);

        $this->baseUrl = implode('/', $urlParams);
        if (!is_numeric($lastParam)) {
            $this->baseUrl.='/'.$lastParam;
        }
    }

    public function getDataSchema(AbstractSchemaRepository $schemaRepository = null)
    {
        return $this->getSchema('data', $schemaRepository);
    }

    public function getExampleSchema(AbstractSchemaRepository $schemaRepository = null)
    {
        return $this->getSchema('example', $schemaRepository);
    }

    /**
     * @param string $schemaName
     * @param AbstractSchemaRepository|null $schemaRepository
     * @return AbstractSchema | false
     */
    public function getSchema($schemaName = 'schema', AbstractSchemaRepository $schemaRepository = null)
    {
        if (!$this->hasSchema($schemaName)) {
            $schemaRepository = $schemaRepository ?? $this->schemaRepository;
            $schema = $schemaRepository->find($this->name, $schemaName);
            $this->schemas[$schemaName] = $schema;
        }

        return $this->schemas[$schemaName];
    }

    public function hasSchema($schemaName)
    {
        return isset($this->schemas[$schemaName]);
    }

    public function setDataSchema(AbstractSchema $schema)
    {
        return $this->setSchema('data', $schema);
    }

    public function setSchema($schemaName, AbstractSchema $schema)
    {
        $this->schemas[$schemaName] = $schema;

        return $this;
    }

    public function mergeDataSchema(AbstractSchema $schema)
    {
        return $this->mergeSchema('data', $schema);
    }

    public function mergeSchema($schemaName, AbstractSchema $newSchema)
    {
        $schema = $this->getSchema($schemaName);
        if ($schema) {
            $schema = $schema->merge($newSchema->toArray());
            $this->setSchema($schemaName, $schema);
        }

        return $this;
    }

    public function accessable(UdiUserInterface $user)
    {
        $access = $user->isAdmin();
        $schema = $this->getDataSchema();
        if($accessGroups = $schema->get('access')){
            if(!in_array('!admin', $accessGroups)){
                $accessGroups[] = 'admin';
            }
            $userGroups = $user->getUserGroups();
            $access = count(array_intersect($userGroups, $accessGroups)) > 0;
            if($access && !$this->filteredByAccess){
                $schemaData = $schema->toArray();
                $schema->set(null, $this->filterByAccess($schemaData, $userGroups));
                $this->filteredByAccess = true;
            }
        }

        return $access;
    }

    private function filterByAccess(array $schema, array $userGroups)
    {
        $result = [];
        foreach($schema as $nodeName => $nodeData){
            $access = true;
            if(isset($nodeData['access'])){
                $accessGroups = $nodeData['access'];
                if(!in_array('!admin', $accessGroups)){
                    $accessGroups[] = 'admin';
                }
                $access = count(array_intersect($userGroups, $accessGroups)) > 0;
            }
            if($access){
                $result[$nodeName] = is_array($nodeData) ? $this->filterByAccess($nodeData, $userGroups) : $nodeData;
            }
        }

        return $result;
    }
}
