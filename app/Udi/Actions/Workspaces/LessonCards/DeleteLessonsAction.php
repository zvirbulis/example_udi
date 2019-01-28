<?php


namespace App\Udi\Actions\Workspaces\LessonCards;


use App\Facades\Service;
use App\Udi\Actions\BaseAction;

class DeleteLessonsAction extends BaseAction
{
    public function run($id)
    {
        try{
            $count = Service::lessonCardService()->removeLessonCardLessons($id);
            return $this->success('Видалено '.$count.' уроків');
        }
        catch (\Exception $e){
            return $this->error('Карточка урока не була видалена через помилку: "'.$e->getMessage().'"');
        }

    }
}