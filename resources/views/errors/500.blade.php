{{-- resources/views/errors/500.blade.php --}}
@extends('errors.layout')

@section('title', 'Error 500 - Server Error')

@section('code', '500')
@section('message', 'Server Error')
@section('description', 'Something went wrong on our servers. We are working to fix the issue.')
