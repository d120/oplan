@extends('layout.default')
@section('content')
<table class=table>
  <thead>
    <tr><th></th><th>Bezeichnung</th><th>Wer macht's?</th><th>Wie viele?</th><th>Wann?</th><th>Dauer?</th></tr>
  </thead>
  <tbody>

@foreach($list as $ak)
<tr class=active>
  <td style="background-color: #{{ $ak->export_farbe }}" width=10></td>
  <td><a href="{{ action('AkListController@show', [$ak->veranstaltung->kuerzel, $ak->slug ?: $ak->id ]) }}">
  {{ $ak->langtitel }}</a></td><td>
  @foreach($ak->leiter as $leiter)
  {{ $leiter->name }}
  @endforeach
</td>
<td>{{ $ak->wieviele_freitext }}</td>
<td>{{ $ak->wann_freitext }}</td>
<td>{{ $ak->dauer_freitext }}</td>
</tr>
<tr class=noborder>
  <td style="background-color: #{{ $ak->export_farbe }}" width=10></td>
  <td colspan=5>Beschreibung: {{ $ak->beschreibung }}</td></tr>

@endforeach

</tbody></table>

<style>
.noborder td{border-top:0 none!important}
</style>
@endsection
