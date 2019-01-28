<?php


namespace App\Udi\Actions\Workspaces\Years;


use App\Facades\Service;
use App\Factories\Queries\LessonCardQueryFactory;
use App\Models\Lesson;
use App\Models\LessonCard;
use App\Udi\Actions\BaseAction;
use App\Udi\Exceptions\UdiProgressActionException;
use App\Udi\Nodes\Workspace\ActionFieldNode;

class RemoveLessonsAction extends BaseAction
{
    private $step;
    private $total;
    private $stats;

    public function __construct(array $params, ActionFieldNode $node)
    {
        parent::__construct($params, $node);
        $this->step = request('step', 0) + 1;
        $this->stats = [
            'lessons' => request('lessons', 0),
            'grades' => request('grades', 0),
            'absents' => request('absents', 0),
            'replacements' => request('replacements', 0)
        ];
    }

    public function run($id)
    {
        try{
            $year = Service::yearService()->getYearOrFail($id);
            $lessonCards = Service::yearService()->getYearLessonCards($year);
            $this->total = ceil($lessonCards->count() / 10);
            if($this->step <= $this->total){
                foreach($lessonCards->slice(($this->step - 1) * 10, 10) as $lessonCard){
                    $this->removeLessons($lessonCard);
                    $this->removeScheduleReplacements($lessonCard);
                }
                $this->progress($this->step, $this->total, $this->stats);
            }

            return $this->success(<<<MESSAGE
Уроки успішно видалено. Всього видалено:
 <br />- уроки: {$this->stats['lessons']}
 <br />- оцінки: {$this->stats['grades']}
 <br />- відсутності: {$this->stats['absents']}
 <br />- заміни: {$this->stats['replacements']}
MESSAGE
);
        }
        catch(\Exception $e){
            if($e instanceof UdiProgressActionException){
                throw $e;
            }
            return $this->error('Уроки не були видалені через помилку: "'.$e->getMessage().'"');
        }
    }

    private function removeLessons(LessonCard $lessonCard)
    {
        foreach($lessonCard->lessons as $lesson){
            /**
             * @var Lesson $lesson
             */
            foreach ($lesson->grades as $grade){
                $grade->delete();
                $this->stats['grades']++;
            }
            foreach($lesson->absents as $absent){
                $absent->delete();
                $this->stats['absents']++;
            }

            $lesson->delete();
            $this->stats['lessons']++;
        }
    }

    private function removeScheduleReplacements(LessonCard $lessonCard)
    {
        foreach($lessonCard->scheduleReplacements as $scheduleReplacement){
            $scheduleReplacement->delete();
            $this->stats['replacements']++;
        }
    }
}