<?php


namespace App\Udi\Actions\Workspaces\StudyPlans;

use App\Facades\Service;
use App\Udi\Actions\BaseAction;

class PerfectCopyAction extends BaseAction
{
    public function run($id)
    {
        try {
            $studyPlan = Service::studyPlanService()->copyStudyPlan($id);
            $this->success('Навчальний план скопійовано');
        }
        catch (\Exception $e){
            return $this->error('План не було скопійовано внаслідок помилки: "'.$e->getMessage().'"');
        }
    }
}
