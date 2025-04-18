@extends('layouts.app')
@section('title', __('app.add_family'))
@section('content')
    <div class="container">
        <h1>{{ __('app.add_family') }}</h1>
        <div class="alert alert-warning">
            <strong>{{ __('Warning!') }}</strong> {{ __('app.family_add_warning') }}
        </div>              
        <form action="{{ route('families.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">{{ __('app.family_name') }}</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="description">{{ __('app.family_description') }}</label>
                <textarea name="description" id="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                @error('description')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="parent_id">{{ __('app.parent_family') }}</label>
                <select name="parent_id" id="parent_id" class="form-control family-select" data-placeholder="{{ __('app.search_for_family') }}">
                    <option value="">{{ __('app.none') }}</option>
                    @if(old('parent_id') && $selectedFamily)
                        <option value="{{ old('parent_id') }}" selected>{{ $selectedFamily->name }}</option>
                    @endif
                </select>
                @error('parent_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">{{ __('app.create_family') }}</button>
        </form>
    </div>
    
@endsection

@section ('ext_css')
<link rel="stylesheet" href="{{ asset('css/plugins/select2.min.css') }}">
@endsection

@section('ext_js')
    <script src="{{ asset('js/plugins/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.family-select').select2({
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                ajax: {
                    url: "{{ route('families.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });
        });
    </script>
@endsection