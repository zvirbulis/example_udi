<?php
/**
 * @var string $active код активного в данный момент раздел
 * @var \App\Services\Personal\UserProfileService $userProfile
 */
?>
<!-- Левая часть -->
<div class="lk-left">

    <!-- Кнопка для моб -->
    <div class="active-lk-menu">
        <i><img src="/images/lk-ico-2.png" alt=" " /></i>
        <span>Мій профіль</span>
    </div>
    <!-- Конец Кнопка для моб -->

    <div class="menu-lk">

        <!--
        <div class="close-choose"></div>
        -->

        <ul>
            @if($userProfile->isAuthUserProfile())
            <li>
                <a class="{{ $active == 'feed' ? 'active' : '' }}" href="{{ route('home.index') }}">
                    <i><img src="{{ asset('images/lk-ico-1.png') }}" alt=" " /></i>
                    <span>Стрічка</span>
                </a>
            </li>
            @endif
            @can('read', $userProfile)
            <li>
                <a class="{{ $active == 'profile' ? 'active' : '' }}" href="{{ $userProfile->getProfileUrl() }}">
                    <i><img src="{{ asset('/images/lk-ico-2.png') }}" alt=" " /></i>
                    <span>
                        {{$userProfile->isAuthUserProfile() ? "Мій профіль" : "Профіль"}}
                    </span>
                </a>
            </li>
            @endcan
            @can('showInfo', $userProfile)
            <li>
                <a class="{{ $active == 'info' ? 'active' : '' }}" href="{{ $userProfile->getInfoUrl() }}">
                    <i><img src="{{ asset('/images/excel.png') }}" alt=" " /></i>
                    <span>Особиста картка</span>
                </a>
            </li>
            @endcan
            @can('showIncidents', $userProfile)
            <li>
                <a class="{{ $active == 'incidents' ? 'active' : '' }}" href="{{ $userProfile->getIncidentsUrl() }}">
                    <i><img src="{{ asset('/images/ico-sad-smile.svg') }}" alt=" " width="16" height="16" /></i>
                    <span>Інциденти</span>
                </a>
            </li>
            @endcan
            {{--@can('showProgress', $userProfile)--}}
                {{--@if($userProfile->getUser()->isStudent())--}}
                    {{--<li>--}}
                        {{--<a class="{{ $active == 'progress' ? 'active' : '' }}" href="{{ $userProfile->getProgressInfoUrl() }}">--}}
                            {{--<i><img src="{{ asset('images/lk-ico-1.png') }}" alt=" " /></i>--}}
                            {{--<span>Табель</span>--}}
                        {{--</a>--}}
                    {{--</li>--}}
                {{--@endif--}}
            {{--@endcan--}}
            @can('showProgressReport', $userProfile)
                @if($userProfile->getUser()->isStudent())
                    <li>
                        <a class="{{ $active == 'progress_report' ? 'active' : '' }}" href="{{ $userProfile->getProgressReportUrl() }}">
                            <i><img src="{{ asset('images/lk-ico-1.png') }}" alt=" " /></i>
                            <span>Звіт про успішність</span>
                        </a>
                    </li>
                 @endif
            @endcan
            @can('showMedInfo', $userProfile)
                @if($userProfile->getUser()->isStudent())
                    <li>
                        <a class="{{ $active == 'medical_information' ? 'active' : '' }}" href="{{ $userProfile->getMedicalInfoUrl() }}">
                            <i><img src="{{ asset('/images/lk-ico-2.png') }}" alt=" " /></i>
                            <span>Медична інформація</span>
                        </a>
                    </li>
                @endif
            @endcan
            @if($userProfile->isAuthUserProfile())
            <li>
                <a class="{{ $active == 'notificationssettings' ? 'active' : '' }}" href="{{ \App\Facades\Service::userService()->getUserNotificationsSettingsUrl(Auth::user()) }}">
                    <i><img src="{{ asset('images/lk-ico-3.png') }}" alt=" " /></i>
                    <span>Налаштування сповіщень</span>
                </a>
            </li>
            @endif
            {{--    <li>
                    <a class="" href="#">
                        <i><img src="{{ asset('images/lk-ico-1.png') }}" alt=" " /></i>
                        <span>Медична карта</span>
                    </a>
                </li>
                <li>
                    <a class="" href="#">
                        <i><img src="{{ asset('images/lk-ico-1.png') }}" alt=" " /></i>
                        <span>Інциденти</span>
                    </a>
                </li>
    --}}
        </ul>
    </div>
</div>
<!-- Конец Левая часть -->
