@extends('layouts.main', ['adaptive' => false, 'active' => 'announcements'])

@section('content')
    <handbook-index :init_url="{{ $init_url }}"></handbook-index>
@endsection
