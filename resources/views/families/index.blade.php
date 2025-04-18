@extends('layouts.app')

@section('content')
    <h2>{{ __('app.all_families') }}</h2>

    <div class="row">
        <div class="col-xs-12">
            <form method="GET" class="form-inline">
                <div class="form-group" style="margin-right: 10px;">
                    <input type="text" name="search" class="form-control"
                           value="{{ $search }}" placeholder="{{ __('app.search_families') }}">
                </div>
                <button type="submit" class="btn btn-default">
                    {{ __('app.search') }}
                </button>
            </form>
        </div>
        
        @auth
            <div class="col-xs-12" style="margin-top: 15px; text-align: right;">
                <a href="{{ route('families.create') }}" class="btn btn-primary">
                    {{ __('app.add_family') }}
                </a>
            </div>
        @endauth    
    </div>

    @if ($families->count())
        <table class="table table-bordered" style="margin-top: 2%">
            <thead>
                <tr>
                    <th>{{ __('app.family') }}</th>
                    <th>{{ __('app.family_description') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($families as $family)
                    <tr>
                        <td>
                            {!! link_to_route('families.show', $family->name, [$family->id]) !!}
                        </td>
                        <td>
                            <div class="description-container">
                                <div id="short-desc-{{ $family->id }}">
                                    {{ \Illuminate\Support\Str::limit($family->description, 100) }}
                                    @if(strlen($family->description) > 100)
                                        <button type="button" class="btn btn-link p-0 toggle-desc" data-id="{{ $family->id }}">{{ __('app.view_all') }}</button>
                                    @endif
                                </div>
                                <div id="full-desc-{{ $family->id }}" style="display:none;">
                                    {{ $family->description }}
                                    <button type="button" class="btn btn-link p-0 toggle-desc" data-id="{{ $family->id }}">{{ __('app.show_less') }}</button>
                                </div>
                            </div>
                        </td>                                          
                        <td>
                            {!! link_to_route('families.show', __('app.view'), [$family->id], ['class' => 'btn btn-xs btn-success']) !!}
                            @can('edit', $family)
                            {!! link_to_route('families.edit', __('app.edit'), [$family->id], ['class' => 'btn btn-xs btn-primary']) !!}
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-center">
            {!! $families->appends(Request::except('page'))->links('pagination::bootstrap-4') !!}
        </div>
        
    @else
        <p>{{ __('app.no_families_found') }}</p>
    @endif

@endsection

@section('ext_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-desc').forEach(function(button) {
            button.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var shortDesc = document.getElementById('short-desc-' + id);
                var fullDesc = document.getElementById('full-desc-' + id);
                
                if (shortDesc.style.display === 'none') {
                    shortDesc.style.display = 'block';
                    fullDesc.style.display = 'none';
                } else {
                    shortDesc.style.display = 'none';
                    fullDesc.style.display = 'block';
                }
            });
        });
    });
    </script>
@endsection