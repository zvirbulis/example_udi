<?php


namespace App\Udi\Actions\Workspaces\LessonCards;


use App\Facades\Service;
use App\Udi\Actions\BaseAction;

class DeleteExtraLessonsAction extends BaseAction
{
    public function run($id)
    {
        try{
            $count = Service::lessonCardService()->removeLessonCardExtraLessons($id);

            if($count){
                return $this->success('Видалено '.$count.' зайвих уроків');
            }
            else{
                return $this->success('Зайвих уроків не знайдено');
            }
        }
        catch (\Exception $e){
            return $this->error('При пошуці зайвих уроків виникла помилка: "'.$e->getMessage().'"');
        }
    }
}