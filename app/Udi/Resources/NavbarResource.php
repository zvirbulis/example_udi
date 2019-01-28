<?php


namespace App\Udi\Resources;

use App\Udi\SchemaRepositories\AbstractSchemaRepository;

class NavbarResource extends AbstractResource
{
    public function __construct(AbstractSchemaRepository $schemaRepository)
    {
        parent::__construct('Navbar', $schemaRepository);
    }
}
