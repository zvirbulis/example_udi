<?php


namespace App\Udi\SchemaRepositories;

use App\Udi\Exceptions\UdiException\UdiException;

class FileSchemaRepository extends AbstractSchemaRepository
{
    protected $paths;
    protected $ext;

    public function setUp(array $paths, $ext = 'json')
    {
        $this->paths = $paths;
        $this->ext = $ext;
    }

    /**
     * @param $resourceName
     * @param $schemaName
     * @return bool|string
     * @throws UdiException
     */
    public function find($resourceName, $schemaName)
    {
        if (!isset($this->paths[$schemaName])) {
            throw new UdiException('Схему з іменем "'.$schemaName.'" не визначено для сховища '.__CLASS__);
        }
        $path = rtrim($this->paths[$schemaName], '/');
        $file = $path.'/'.$resourceName.'.'.$this->ext;
        if (file_exists($file)) {
            return $this->newInstance($schemaName, file_get_contents($file));
        }

        return false;
    }
}
