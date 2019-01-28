<?php


namespace App\Udi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Udi\Http\Response;
use App\Udi\Resources\AbstractResource;
use Illuminate\Auth\Access\AuthorizationException;

class ResourceController extends Controller
{
    public function resource(AbstractResource $resource)
    {
        $schema = $resource->getDataSchema() ?: $resource->getExampleSchema();

        return $schema
            ? Response::ok($this->processData($schema->toArray()))
            : Response::notFound();
    }

    protected function processData(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = is_array($value)
                ? $this->processData($value)
                : $this->processDataValue($value);
        }

        return $data;
    }

    protected function processDataValue($value)
    {
        $hasTpl = (strpos($value, '#') !== false);
        if ($hasTpl && preg_match_all('/#ENV_([A-Za-z0-9_]+)#/', $value, $matches)) {
            foreach ($matches[1] as $k => $envKey) {
                //TODO сделать белый список параметров, которые можно достать из переменных окружения. А пока-что это дырка
                if ($env = env($envKey)) {
                    $value = str_replace($matches[0][$k], $env, $value);
                }
            }
        }

        return $value;
    }

    /**
     * @param AbstractResource $resource
     * @throws AuthorizationException
     */
    protected function access(AbstractResource $resource)
    {
        if(!$resource->accessable(\Auth::user())){
            throw new AuthorizationException("Access denied");
        }
    }
}
