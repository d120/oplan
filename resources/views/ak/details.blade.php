@extends('layout.default')
@section('content')
<title>{{ $ak->kurztitel }}</title>
<div style="background-color: #{{ $ak->export_farbe }}; height: 10px"></div>

<h2><a href="{{action('AkListController@index',[$ak->veranstaltung->kuerzel])}}">{{ $ak->veranstaltung->kuerzel }}</a>
   &#187; {{ $ak->langtitel }}</h2>

<p>Scheduled Date: {{ $ak->von }} - {{ $ak->bis }}</p>

<p>Räume:</p>
<ul>
  @forelse($ak->raumbedarf as $r)
  <li>{{ $r->kommentar }} ({{ $r->min_platz }}) --> {{ $r->raum }}</li>
  @empty
  <li>(keine)</li>
  @endforelse
</ul>

<p>{{ $ak->beschreibung }}</p>

<ul>
  @foreach($ak->leiter as $leiter)
  <li>{{ $leiter->name }} ({{ $leiter->organisation->name }})</li>
  @endforeach
</ul>

@endsection
