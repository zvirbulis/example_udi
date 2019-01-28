<?php


namespace App\Udi\SchemaRepositories;

use App\Udi\Exceptions\UdiException;
use App\Udi\Schemas\AbstractSchema;

abstract class AbstractSchemaRepository
{
    private $schema;

    public function __construct(AbstractSchema $schema)
    {
        $this->schema = $schema;
    }

    abstract public function find($resourceName, $schemaName);

    /**
     * @param $resourceName
     * @param $schemaName
     * @return mixed
     * @throws UdiException
     */
    public function findOrFail($resourceName, $schemaName)
    {
        $schema = $this->find($resourceName, $schemaName);
        if (!$schema) {
            throw new UdiException('Схему "'.$schemaName.'" для ресурсу "'.$resourceName.'" не визначено!');
        }

        return $schema;
    }

    public function newInstance($schemaName, $schemaData)
    {
        $class = get_class($this->schema);

        return new $class($schemaName, $schemaData);
    }
}
