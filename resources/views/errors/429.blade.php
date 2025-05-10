{{-- resources/views/errors/429.blade.php --}}
@extends('errors.layout')

@section('title', 'Error 429 - Too Many Requests')

@section('code', '429')
@section('message', 'Too Many Requests')
@section('description', 'You have made too many requests recently. Please wait before trying again.')
