<?php


namespace App\Udi\Actions\Workspaces\LessonCards;


use App\Facades\Service;
use App\Generators\LessonGenerator;
use App\Udi\Actions\BaseAction;
use App\Udi\Exceptions\UdiException;

class GenerateLessonsAction extends BaseAction
{

    public function run($id)
    {
        try {
            $lessonCard = Service::lessonCardService()->getLessonCardOrFail($id);
            if (!($studentClass = Service::lessonCardService()->getLessonCardFirstStudentClass($lessonCard))) {
                throw new UdiException('Неможливо визначити рік для створення уроків. Зазначена карточка не прив\'язана до учбового класу. 
                Або прив\'язана до потоку без учбових класів');
            }
            $year = $studentClass->year;
            $generator = new LessonGenerator();
            $generator->generateByLessonCardForYear($lessonCard, $year);

            return $this->success('Уроки успішно згенеровано. К-ть згенерованих уроків: '.$generator->getLastGenerateLessonsCount());
        }
        catch (\Exception $e){
            return $this->error('Уроки не було згенеровано через помилку: "'.$e->getMessage().'"');
        }
    }
}