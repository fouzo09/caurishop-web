@extends('shop.layouts.app')

@section('title', 'Inscription — CAURISHOP')

@section('content')
  @include('shop.auth._panel', ['activeTab' => 'register'])
@endsection
