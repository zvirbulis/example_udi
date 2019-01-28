<?php


namespace App\Udi\Actions\Workspaces;

use App\Udi\Actions\BaseAction;
use App\Udi\Builders\QueryBuilder;
use App\Udi\Exceptions\UdiException;

class UpdateFieldsAction extends BaseAction
{
    public function run($id)
    {
        $settings = $this->node->getSettings();
        if (!isset($settings['primary'])) {
            throw new UdiException('Дія оновлення повинна мати первинний ключ в налаштуваннях');
        }
        if (!isset($settings['values'])) {
            throw new UdiException('Дія оновлення повинна мати значення для оновлення');
        }
        $primary = $settings['primary'];
        $values = $settings['values'];

        (new QueryBuilder())
            ->initModel($primary)
            ->values($values)
            ->update($id);
    }
}
