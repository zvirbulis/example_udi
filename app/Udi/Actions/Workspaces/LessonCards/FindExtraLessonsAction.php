<?php


namespace App\Udi\Actions\Workspaces\LessonCards;


use App\Facades\Service;
use App\Models\Lesson;
use App\Udi\Actions\BaseAction;
use Illuminate\Support\Collection;

class FindExtraLessonsAction extends BaseAction
{
    public function run($id)
    {
        try{
            $extraLessons = Service::lessonCardService()->findLessonCardExtraLessons($id);
            if($extraLessons->isEmpty()){
                return $this->success('Зайвих уроків не знайдено');
            }

            $lessonsStat = $this->getLessonsStat($extraLessons);
            $message = <<<MESSAGE
Знайдено <b>$lessonsStat->count</b> зайвих уроків. Серед них:
<br /> - оцінок: $lessonsStat->grades 
<br /> - відсутностей: $lessonsStat->absents
<br /> - коментів: $lessonsStat->comments
<br /> - замін: $lessonsStat->replacements
<br />Зайві уроки проходять в наступні дні: <br />
MESSAGE;
            foreach($lessonsStat->placements as $lessonCardId => $dates){
                $message.='<div style="float: left">';
                foreach($dates as $date){
                    $message.="<div style='width: 70px; float: left; border: 1px solid white; padding: 1px; margin: 1px;'>".$date->format('d-m-Y')."</div>";
                }
                $message.='</div><br clear="all" />';
            }
            return $this->success($message);
        }
        catch (\Exception $e){
            return $this->error('При пошуці зайвих уроків виникла помилка: "'.$e->getMessage().'"');
        }
    }

    /**
     * @param Collection|Lesson[] $lessons
     */
    public function getLessonsStat(Collection $lessons)
    {
        $stat = [
            'count' => $lessons->count(),
            'grades' => 0,
            'absents' => 0,
            'comments' => 0,
            'replacements' => 0,
            'placements' => []
        ];
        $usedLessonCards = [];
        foreach($lessons as $lesson){
            $stat['grades'] += $lesson->grades->count();
            $stat['absents'] += $lesson->absents->count();
            $stat['comments'] += $lesson->comments->count();
            $lessonCard = $lesson->lessonCard;
            if(!isset($usedLessonCards[$lessonCard->id])){
                $stat['replacements'] += $lessonCard->scheduleReplacements->count();
                $usedLessonCards[$lessonCard->id] = true;
            }
            $stat['placements'][$lessonCard->id][] = $lesson->date_processed;
        }

        return (object)$stat;
    }
}