@extends('layouts.app')

@section('content')
<div class="container">
<ul class="list-group mt-5">
@foreach ($pages->data as $page)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        {{$page->name}}
        <span class="badge-pill"><a href="{{ route('link', ['page_id'=>$page->id, 'page_name'=>$page->name, 'page_token'=>$page->access_token]) }}" class="btn btn-info">Submit</a></span>
    </li>
@endforeach
</ul>

</div>
@endsection
