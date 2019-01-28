<?php


namespace App\Udi;

use App\Models\Announcement;
use App\Models\BreakActivity;
use App\Models\CarryDay;
use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\File;
use App\Models\Grade;
use App\Models\Holiday;
use App\Models\Incident;
use App\Models\Integration;
use App\Models\KnowledgeRealm;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\LessonNum;
use App\Models\LessonNumSchema;
use App\Models\LessonNumSchemaParam;
use App\Models\LessonPlan;
use App\Models\RkeeperTransaction;
use App\Models\ScheduleSlot;
use App\Models\SchoolTheme;
use App\Models\SKDHardware;
use App\Models\SKDRecord;
use App\Models\SocialAccount;
use App\Models\SpecialGradeType;
use App\Models\Stream;
use App\Models\StudentGroup;
use App\Models\StudentParentProfile;
use App\Models\StudentProfile;
use App\Models\StudyClass;
use App\Models\Subject;
use App\Models\SubjectLoad;
use App\Models\SubjectType;
use App\Models\TeacherProfile;
use App\Models\TildaArticle;
use App\Models\IncidentType;
use App\Models\UserGroup;
use App\Models\UserIntegration;
use App\Models\Users\Student;
use App\Models\Year;
use App\Models\StudentClass;
use App\Models\StudyPlan;
use App\Models\YearInterval;
use App\Udi\Actions\Workspaces\LessonCards\DeleteExtraLessonsAction;
use App\Udi\Actions\Workspaces\LessonCards\DeleteLessonsAction;
use App\Udi\Actions\Workspaces\LessonCards\FindExtraLessonsAction;
use App\Udi\Actions\Workspaces\UpdateFieldsAction;
use App\Udi\Actions\Workspaces\Users\AuthorizeAction;
use App\Udi\Actions\Workspaces\Users\ChattingAction;
use App\Udi\Actions\Workspaces\Users\InviteAction;
use App\Udi\Actions\Workspaces\Users\PerfectCopyAction;
use App\Udi\Actions\Workspaces\Users\ProfileAction;
use App\Udi\Actions\Workspaces\Users\RestoreLoginAction;
use App\Udi\Actions\Workspaces\Users\StuffListAction;
use App\Udi\Actions\Workspaces\Users\DepartmentsListAction;
use App\Udi\Actions\Workspaces\Years\GenerateLessonsAction;
use App\Udi\Actions\Workspaces\Years\RemoveLessonsAction;
use App\Udi\Builders\WorkspaceDataBuilder;
use App\Udi\Customization\FileUploader;
use App\Udi\Exceptions\UdiDuplicateEntryException;
use App\Udi\Exceptions\UdiException;
use App\Udi\Exceptions\UdiForeignKeysDeleteException;
use App\Udi\Exceptions\UdiWorkspaceResourceException;
use App\Udi\Interfaces\WorkspaceDataBuilderInterface;
use App\Udi\Nodes\Workspace\ActionFieldNode;
use App\Udi\SchemaRepositories\AbstractSchemaRepository;
use App\Udi\SchemaRepositories\FileSchemaRepository;
use App\Udi\Schemas\AbstractSchema;
use App\Udi\Schemas\JsonSchema;
use App\Udi\Nodes\WorkspaceNode;
use App\Udi\Support\PivotModel;
use App\Udi\Uploaders\BaseUploader;
use Illuminate\Database\Eloquent\Model;

class IoC
{
    protected static $container = [
        'models' => [
            'ClassRoom' => ClassRoom::class,
            'Grade' => Grade::class,
            'Student' => Student::class,
            'Subject' => Subject::class,
            'KnowledgeRealm' => KnowledgeRealm::class,
            'Lesson' => Lesson::class,
            'Year' => Year::class,
            'Holiday' => Holiday::class,
            'User' => Customization\Models\User::class,
            'UserGroup' => UserGroup::class,
            'StudentGroup' => StudentGroup::class,
            'StudentProfile' => Customization\Models\StudentProfile::class,
            'StudentParentProfile' => StudentParentProfile::class,
            'StudentClass' => StudentClass::class,
            'StudyPlan' => Customization\Models\StudyPlan::class,
            'StudyClass' => StudyClass::class,
            'SubjectLoad' => SubjectLoad::class,
            'LessonPlan' => LessonPlan::class,
            'LessonNum' => LessonNum::class,
            'Announcement' => Announcement::class,
            'SubjectType' => SubjectType::class,
            "TeacherProfile" => TeacherProfile::class,
            'File' => File::class,
            "TildaArticle" => TildaArticle::class,
            'Integration' => Integration::class,
            'UserIntegration' => UserIntegration::class,
            'SocialAccount' => SocialAccount::class,
            'LessonActivity' => LessonActivity::class,
            'LessonCard' => \App\Udi\Customization\Models\LessonCard::class,
            'YearInterval' => YearInterval::class,
            'ScheduleSlot' => ScheduleSlot::class,
            'BreakActivity' => BreakActivity::class,
            'Department' => Department::class,
            'CarryDay' => CarryDay::class,
            'RkeeperTransactions' => RkeeperTransaction::class,
            'SKDRecord' => SKDRecord::class,
            'SKDHardware' => SKDHardware::class,
            'LessonNumSchema' => LessonNumSchema::class,
            'Stream' => Stream::class,
            'Incident' => Incident::class,
            'IncidentType' => IncidentType::class,
            'SpecialGradeType' => SpecialGradeType::class,
            'SchoolTheme' => SchoolTheme::class
        ],
        'pivots' => [
            'ClassRoomSubject' => ['table' => 'classroom_subject', 'fillable' => ['class_room_id', 'subject_id']],
            'AnnouncementUser' => ['table' => 'announcement_user', 'fillable' => ['announcement_id', 'user_id']],
            'StudentGroupStudyClass' => ['table' => 'student_group_study_class', 'fillable' => ['student_group_id', 'study_class_id']],
            'StudentStudentParent' => ['table' => 'student_student_parent', 'fillable' => ['student_parent_id', 'student_id']],
            'StreamStudentClass' => ['table' => 'stream_student_class', 'fillable' => ['stream_id', 'student_class_id']]
        ],
        'actions' => [
            'update' => UpdateFieldsAction::class,
            'profile' => ProfileAction::class,
            'authorize' => AuthorizeAction::class,
            'chatting' => ChattingAction::class,
            'restore_login' => RestoreLoginAction::class,
            'invite' => InviteAction::class,
            'stuff_list' => StuffListAction::class,
            'departments_list' => DepartmentsListAction::class,
	        'generate_lessons' => GenerateLessonsAction::class,
            'remove_lessons' => RemoveLessonsAction::class,
            'perfect_copy' => \App\Udi\Actions\Workspaces\StudyPlans\PerfectCopyAction::class,
            'find_extra_lessons' => FindExtraLessonsAction::class,
            'remove_extra_lessons' => DeleteExtraLessonsAction::class,
            'lesson_card_delete_lessons' => DeleteLessonsAction::class,
            'generate_lesson_card_lessons' => \App\Udi\Actions\Workspaces\LessonCards\GenerateLessonsAction::class
        ],
        'uploaders' => [
            'default' => BaseUploader::class,
            'kanschool' => FileUploader::class
        ],
        'exceptions' => [
            'delete-foreign-keys' => UdiForeignKeysDeleteException::class,
            'create-duplicate-entry' => UdiDuplicateEntryException::class
        ]
    ];

    /**
     * @param null $schema
     * @return AbstractSchemaRepository
     */
    public static function schemaRepository(AbstractSchema $schema = null) : AbstractSchemaRepository
    {
        $schema = $schema ?? new JsonSchema();
        $repository = new FileSchemaRepository($schema);
        $repository->setUp([
            'schema' => config('udi.schemas_path'),
            'data' => config('udi.data_path'),
            'example' => config('udi.examples_path')
        ]);

        return $repository;
    }

    public static function workspaceBuilder(WorkspaceNode $skeleton) : WorkspaceDataBuilderInterface
    {
        return new WorkspaceDataBuilder($skeleton);
    }

    /**
     * @param $code
     * @param array $attributes
     * @return Model
     * @throws UdiException
     */
    public static function model($code, $attributes = []) : Model
    {
        $class = self::modelClass($code);
        $model = new $class();
        if (!$model instanceof Model) {
            throw new UdiException('Модель з кодом = "'.$code.'" має бути "'.Model::class.'"');
        }
        if ($attributes) {
            $model->fill($attributes);
        }

        return $model;
    }

    /**
     * @param $code
     * @return string
     * @throws UdiException
     */
    public static function modelClass($code) : string
    {
        $models = static::$container['models'];
        if (!isset($models[$code])) {
            if (static::hasPivot($code)) {
                $pivot = static::pivot($code);
                static::$container['models'][$code] = get_class($pivot);

                return static::modelClass($code);
            }
            throw new UdiException('Модель з кодом = "'.$code.'" не визначена в IoC');
        }


        return $models[$code];
    }

    public static function hasPivot($code)
    {
        return isset(static::$container['pivots'][$code]);
    }

    public static function pivot($code)
    {
        if (!static::hasPivot($code)) {
            throw new UdiException('Блок з кодом = "'.$code.'" не визначечний в IoC');
        }
        $model = new class([]) extends PivotModel {
        };
        $pivot = static::$container['pivots'][$code];
        $model::setUp($pivot);

        return $model;
    }

    public static function action($type, array $params = [], ActionFieldNode $node = null)
    {
        $actions = static::$container['actions'];
        if (!isset($actions[$type])) {
            throw new UdiException('Дія з типом = "'.$type.'" не визначена в IoC');
        }

        $actionClass = $actions[$type];

        return new $actionClass($params, $node);
    }

    public static function uploader($uploaderCode, array $params = [])
    {
        $uploaders = static::$container['uploaders'];
        if (!isset($uploaders[$uploaderCode])) {
            throw new UdiException('Загружчик з кодом = "'.$uploaderCode.'" не визначений в IoC');
        }

        $uploaderClass = $uploaders[$uploaderCode];

        $disk = isset($params['disk']) ? $params['disk'] : '';
        $dir = isset($params['dir']) ? $params['dir'] : '';
        $subdir = isset($params['subdir']) && $params['subdir'] === true;

        return new $uploaderClass($disk, $dir, $subdir);
    }

    public static function exception(string $code, array $arguments = [])
    {
        $exceptions = self::exceptions();
        if(!isset($exceptions[$code])){
            throw new UdiException('Exception with code = "'.$code.'" not defined in IoC');
        }

        $e = new $exceptions[$code];
        if($e instanceof UdiWorkspaceResourceException){
            $e->setWorkspace($arguments[0]);
        }

        return $e;
    }

    public static function exceptions()
    {
        return self::$container['exceptions'];
    }
}
