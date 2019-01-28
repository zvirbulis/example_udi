<?php


namespace App\Udi\Actions\Workspaces\Years;


use App\Exceptions\KanSchoolException;
use App\Facades\Service;
use App\Generators\LessonGenerator;
use App\Udi\Actions\BaseAction;
use App\Udi\Exceptions\UdiProgressActionException;
use App\Udi\Nodes\Workspace\ActionFieldNode;

class GenerateLessonsAction extends BaseAction
{
    private $yearService;

    public function __construct(array $params, ActionFieldNode $node)
    {
        parent::__construct($params, $node);
        $this->yearService = Service::yearService();
    }

    public function run($id)
	{
	    $lessonsCount = request('lessons_count', 0);
	    $week = request('week', 1);
        $generator = new LessonGenerator();
        try{
            $year = $this->yearService->getYearOrFail($id);
            $weeks = $this->yearService->getYearWeeksCount($year);

            if($week <= $weeks){
                $errors = $generator->generateByWeek($year, $week);
                $lessonsCount += $generator->getLastGenerateLessonsCount();
                if(!empty($errors)){
                    throw new KanSchoolException(implode("\n", $errors));
                }
                $this->progress($week, $weeks, ['week' => $week + 1, 'lessons_count' => $lessonsCount]);
            }

            return $this->success('Уроки успішно згенеровано. К-ть згенерованих уроків: '.$lessonsCount);
        }
        catch (\Exception $e){
            if($e instanceof UdiProgressActionException){
                throw $e;
            }
            return $this->error('Уроки не було згенеровано через помилку: "'.$e->getMessage().'"');
        }
	}
}