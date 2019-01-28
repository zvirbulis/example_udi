@php
    $schoolTheme = app('SchoolTheme');
    $header['theme']['ico32x32'] = $schoolTheme->getSchoolIcons()['ico32x32'];
@endphp
<app-header :header_data="{{ json_encode($header ?? []) }}"></app-header>
{{-- <app-menu></app-menu> --}}
<div class="left-panel" style="background-image: url({{$schoolTheme->getMenuIconImage()}}),url({{$schoolTheme->getMenuBackgroundImage()}})">
    <div class="panel-menu">
        <ul>
            <li class="have-submenu">
                <a>
                    <i><img src="/images/menu-ico-1.png" alt=""></i>
                    Розклад
                </a>
                <ul>
                    @can('access', \App\Services\Schedule\StudentClassScheduleService::class)
                    <li>
                        <a href="{{ route('schedule.class.index') }}">Розклад школи</a>
                    </li>
                    @endcan
                    @can('access', \App\Services\Schedule\TeacherScheduleService::class)
                        @if(\Auth::user()->isTeacher())
                        <li>
                            <a href="{{ route('schedule.teacher.index') }}">Розклад вчителя</a>
                        </li>
                        @endif
                    @endcan
                    @can('access', \App\Services\Schedule\StudentScheduleService::class)
                        <li>
                            <a href="{{ route('schedule.student.index') }}">Розклад учня</a>
                        </li>
                    @endcan
                    @can('access', \App\Services\Schedule\ClassRoomScheduleService::class)
                    <li>
                        <a href="{{ route('schedule.classroom.index') }}">Розклад аудиторії</a>
                    </li>
                    @endcan
                </ul>
            </li>
            @if(Gate::check('access', 'StudentParentJournal') || Gate::check('access', \App\Services\DaybookService::class))
                <li class="have-submenu">
                    <a><i><img src="/images/menu-ico-3.png" alt=""></i>Успішність</a>
                    <ul>
                        @can('access', 'StudentParentJournal')
                            <li class="hide-for-mobile">
                                <a href="{{ route('studentparent.journal.index') }}">Журнал успішності</a>
                            </li>
                        @endcan
                        @can('access', \App\Services\DaybookService::class)
                            <li>
                                <a href="{{ route('daybook.index') }}">Щоденник</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            @if(Gate::check('read', 'DirectorInstruments'))
                <li class="have-submenu">
                    <a><i><img src="/images/menu-ico-3.png" alt=""></i>Інструменти директора</a>
                    <ul>
                        @can('access', 'TeachersList')
                        <li class="hide-for-mobile">
                            <a href="{{ route('teachers.list') }}">Список працівників школи</a>
                        </li>
                        @endcan
                        @can('access', 'TeachersList')
                        <li>
                            <a href="{{ route('teachers.departments') }}">Штатний розклад</a>
                        </li>
                        @endcan
                        @can('access', \App\Models\StudentClass::class)
                        <li>
                            <a href="{{ route('studentclass.index') }}">Список учнів школи</a>
                        </li>
                        @endcan
                        @can('access', \App\Services\Reports\StudentProgressReportService::class)
                        <li>
                            <a href="{{ route('studentclass.progress') }}">Місячні звіти успішності</a>
                        </li>
                        @endcan
                    </ul>
                </li>
            @endif

            @if(Gate::check('read', 'AdminInstruments'))
                <li class="have-submenu">
                    <a><i><img src="/images/menu-ico-3.png" alt=""></i>Інструменти адміністратора</a>
                    <ul>
                        <li class="hide-for-mobile">
                            <a href="/udi/workspaces/Users">Користувачі</a>
                        </li>
                        <li>
                            <a href="{{route('accessrules.index')}}">Налаштування прав достопу</a>
                        </li>
                    </ul>
                </li>
            @endif


            @can('access', 'HeadTeacherInstruments')
                <li class="have-submenu">
                    <a><i><img src="/images/menu-ico-3.png" alt=""></i>Інструменти завуча</a>
                    <ul>
                        @can('access', \App\Services\Schedule\Constructors\HourlyLoadConstructorService::class)
                    	    <li class="hide-for-mobile">
                        	<a href="{{ route('hourly_load.distribution.index') }}">Редактор часового навантаження</a>
                    	    </li>
                        @endcan

                        @can('access', \App\Services\Schedule\Constructors\ScheduleConstructorService::class)
                        <li class="hide-for-mobile">
                            <a href="{{ route('schedule.constructor.index') }}">Редактор розкладу</a>
                        </li>
                        @endcan
                        @can('access', \App\Services\Schedule\ReplacementsScheduleService::class)
                            <li class="hide-for-mobile">
                                <a href="{{ route('schedule.replacements.index') }}">Редактор замін</a>
                            </li>
                        @endcan
                        @can('access', 'Udi')
                            <li class="hide-for-mobile">
                                <a href="{{ route('udi.workspace.list', ['workspace' => 'StudyPlans']) }}">Навчальні плани</a>
                            </li>
                        @endcan
                        @can('access', \App\Services\Schedule\Constructors\HourlyLoadConstructorService::class)
                            <li class="hide-for-mobile">
                                <a href="{{ route('roadmap.index') }}">Roadmap</a>
                            </li>
                        @endcan

                    </ul>
                </li>
            @endcan

            @can('access', \App\Services\CanteenService::class)
                <li class="have-submenu">
                    <a><i><img src="/images/menu-ico-3.png" alt=""></i>Їдальня</a>
                    <ul>
                        @if(config('integrations.rkeeper.enable'))
                        <li>
                            <a href="{{ route('canteen.index') }}">Баланс</a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('canteen.news') }}">Меню їдальні</a>
                        </li>
                    </ul>
                </li>
            @endcan

            @if(config('messenger.enable'))
            <li>
                <app-menu-new-messages></app-menu-new-messages>
            </li>
            @endif

            @can('access', 'Udi')
                <li class="open hide-for-mobile">
                    <a href="{{ route('udi.navbar.index') }}"><i><img src="/images/menu-ico-2.png" alt=""></i>Довідники</a>
                </li>
            @endif

            <li class="have-submenu">
                <a>Гімназія А+</a>
                <ul>
                    @foreach(\App\Facades\Service::tildaArticleService()->getMenuItems() as $item)
                        <li>
                            <a href="{{$item['link']}}">
                                @if($item['icon'])
                                    <i><img src="{{$item['icon']}}" alt=""></i>
                                @endif
                                {{$item['text']}}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>




            <li>
                <a href="https://www.youtube.com/playlist?list=PLt5Vw4v-s1x2GDEqPLxjixKDudu9FIrXC"> FAQ </a>
            </li>

            <li>
	        <a href="{{route('auth.logout')}}">
    	            Вихід
        	</a>
            </li>

        </ul>
    </div>
</div>

