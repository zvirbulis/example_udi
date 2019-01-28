<?php
/**
 * Class StudyPlan
 * @package App\Models
 */

namespace App\Udi\Customization\Models;
use App\Facades\Service;
/**
 * Модель для сущности "Учебный план"
 *
 * Учебный план включает в себя набор предустановленной недельной нагрузки для каждого предмета для различных
 * учебных классов
 * Например, есть 2 учебных плана:
 * - Учебный план для 5-А и 5-Б классов по программе "Интелект" (Математика - 10 часов, Английский - 5 часов)
 * - Учебный план для 5-В класса по стандартной программе (Математика - 8 часов, Английский - 3 часа)
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $year_id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StudentClass[] $studentClasses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SubjectLoad[] $subjectLoads
 * @property-read \App\Models\Year $year
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StudyPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StudyPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StudyPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StudyPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StudyPlan whereYearId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LessonPlan[] $lessonPlans
 */

class StudyPlan extends \App\Models\StudyPlan
{
    public function delete()
    {
        return Service::studyPlanService()->deleteStudyPlan($this->id);
    }
}
