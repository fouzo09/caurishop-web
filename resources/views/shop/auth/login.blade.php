@extends('shop.layouts.app')

@section('title', 'Connexion — CAURISHOP')

@section('content')
  @include('shop.auth._panel', ['activeTab' => 'login'])
@endsection
