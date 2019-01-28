<div class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav">
            @can('read', \App\Models\LessonPlan::class)
            <li class="nav-item">
                <a class="nav-link" href="{{route('teacher_plan.index')}}">
                    <i class="icon-speedometer"></i>
                    План вчителя
                </a>
                <a class="nav-link" href="{{route('schedule.student.index')}}">
                    <i class="icon-speedometer"></i>
                    Розклад учня
                </a>
            </li>
            @endcan

            <li class="nav-item">
                <a class="nav-link" href="{{route('schedule.teacher.index')}}">
                    <i class="icon-speedometer"></i>
                    Розклад вчителя
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{route('schedule.class.index')}}">
                    <i class="icon-speedometer"></i>
                    Загальний розклад
                </a>
            </li>

        </ul>
    </nav>
</div>