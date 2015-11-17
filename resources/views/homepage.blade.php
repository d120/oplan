@extends('layout.default')
@section('content')

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            .content {
                text-align: center;
                vertical-align: middle;
            }

            .title {
                font-size: 76px;
                font-family: 'Lato';
                font-weight: 100;
            }
        </style>
        <div class="row">
          <div class="col-md-8">
            <div class="content">
                <div class="title">Willkommen</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="list-group">
              @foreach($veranst as $d)
              <a href="{{ action('AkListController@index', [$d->kuerzel])}}" class="list-group-item">{{ $d->kuerzel}}</a>
              @endforeach
            </div>
          </div>
        </div>
            
@endsection