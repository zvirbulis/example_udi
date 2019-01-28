<?php


namespace App\Udi\Customization\Models;


class LessonCard extends \App\Models\LessonCard
{
    public static function boot()
    {
        parent::boot();
        //self::observe(LessonCardObserver::class);
    }
}

class LessonCardObserver
{
    private static $deleting = [];

    public function deleting(LessonCard $lessonCard)
    {
        \DB::beginTransaction();
        $this->removeLessons($lessonCard);
        $this->removeScheduleReplacements($lessonCard);
        self::$deleting[$lessonCard->id] = true;
    }

    public function deleted(LessonCard $lessonCard)
    {
        if(isset(self::$deleting[$lessonCard->id])){
            \DB::commit();
            unset(self::$deleting[$lessonCard->id]);
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
            }
            foreach($lesson->absents as $absent){
                $absent->delete();
            }

            $lesson->delete();
        }
    }

    private function removeScheduleReplacements(LessonCard $lessonCard)
    {
        foreach($lessonCard->scheduleReplacements as $scheduleReplacement){
            $scheduleReplacement->delete();
        }
    }
}