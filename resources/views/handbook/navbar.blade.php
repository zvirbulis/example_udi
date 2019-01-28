@extends('layouts.main', ['adaptive' => false, 'active' => 'announcements'])
@php
//dd($navbar_items);
@endphp
@section('content')
    <div class="container handbooks-homepage">
        <div class="background-img">

        </div>
        <div class="head-container">
            <h1>Ресурси школи</h1>
            <p>
                @php
                    $themeService = app('SchoolTheme');
                @endphp
                {!! $themeService->getSchoolDescription() !!}
            </p>
            <div class="bt blue">
                <a id="make-question-button" href="javascript:void(0);"> <span>Задати питання</span></a>
            </div>
        </div>
        <div class="grid-container">
            @foreach($navbar_items as $key => $item)
                @if(!empty($item["list"]))
                <div class="grid-block {{$key}}">
                    <span class="toggle-menu-mobile"></span>
                    <div class="grid-block-in">
                        @if (isset($item['header']) && strlen($item['header']) > 0)
                            <div class="block-header-md">
                                {!! $item['header'] !!}
                            </div>
                        @endif
                        @if (isset($item['sub_header']) && strlen($item['sub_header']) > 0)
                            <div class="block-header-subheader"><a href="{{$item['header_url']}}">{{$item['sub_header']}}</a></div>
                        @endif
                        @if (isset($item['heading_text']) && strlen($item['heading_text']) > 0)
                            <div class="block-article-name">{{$item['heading_text']}}</div>
                        @endif
                        @if(isset($item["text"]) && strlen($item['text']) > 0)
                            <div class="block-article-text">{{$item["text"]}}</div>
                        @endif
                        <div class="block-ico {{$key}}"></div>
                        <ul>
                            @foreach($item["list"] as $item_list)
                                <li><a href="{{$item_list["url"]}}">{{$item_list["name"]}}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection
