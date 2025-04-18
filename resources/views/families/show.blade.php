@extends('layouts.app')

@section('content')
    <h2 class="">
        {{ $family->name }}
        @can('edit', $family)
            <a href="{{ route('families.edit', $family->id) }}" class="btn btn-sm btn-primary ml-2">
                {{ __('app.edit') }}
            </a>
        @endcan
        @if ($family->description)
            <div class="description-container">
                <div id="short-desc-main-{{ $family->id }}">
                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($family->description, 100) }}</small>
                    @if(strlen($family->description) > 100)
                        <button type="button" class="btn btn-link p-0 toggle-desc" data-target="main-{{ $family->id }}">{{ __('app.view_all') }}</button>
                    @endif
                </div>
                <div id="full-desc-main-{{ $family->id }}" style="display:none;">
                    <small class="text-muted">{{ $family->description }}</small>
                    <button type="button" class="btn btn-link p-0 toggle-desc" data-target="main-{{ $family->id }}">{{ __('app.show_less') }}</button>
                </div>
            </div>
        @endif
    </h2>

    @if ($ancestors->isNotEmpty())
        <p>
            <strong>{{ __('app.family_lineage') }}:</strong>
            @foreach ($ancestors as $ancestor)
                {!! link_to_route('families.show', $ancestor->name, [$ancestor->id]) !!}
                @if ($ancestor->description)
                    <span class="description-container">
                        <span id="short-desc-anc-{{ $ancestor->id }}">
                            <small class="text-muted">({{ \Illuminate\Support\Str::limit($ancestor->description, 50) }})</small>
                            @if(strlen($ancestor->description) > 50)
                                <button type="button" class="btn btn-link p-0 toggle-desc" data-target="anc-{{ $ancestor->id }}">{{ __('app.view_all') }}</button>
                            @endif
                        </span>
                        <span id="full-desc-anc-{{ $ancestor->id }}" style="display:none;">
                            <small class="text-muted">({{ $ancestor->description }})</small>
                            <button type="button" class="btn btn-link p-0 toggle-desc" data-target="anc-{{ $ancestor->id }}">{{ __('app.show_less') }}</button>
                        </span>
                    </span>
                @endif
                @if (!$loop->last) → @endif
            @endforeach
            → <strong>{{ $family->name }}</strong>
        </p>
    @endif

    <hr>

    @if ($children->isNotEmpty())
        <h4>{{ __('app.direct_children_families') }}:</h4>
        <ul class="list-unstyled">
            @foreach ($children as $child)
                <li class="mb-2">
                    → {!! link_to_route('families.show', $child->name, [$child->id]) !!}
                    @if ($child->description)
                        <div class="description-container">
                            <div id="short-desc-child-{{ $child->id }}">
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($child->description, 100) }}</small>
                                @if(strlen($child->description) > 100)
                                    <button type="button" class="btn btn-link p-0 toggle-desc" data-target="child-{{ $child->id }}">{{ __('app.view_all') }}</button>
                                @endif
                            </div>
                            <div id="full-desc-child-{{ $child->id }}" style="display:none;">
                                <small class="text-muted">{{ $child->description }}</small>
                                <button type="button" class="btn btn-link p-0 toggle-desc" data-target="child-{{ $child->id }}">{{ __('app.show_less') }}</button>
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if ($descendants->isNotEmpty())
        <h4>{{ __('app.all_descendant_families') }}:</h4>
        <ul class="list-unstyled">
            @foreach ($descendants as $descendant)
                <li class="mb-2">
                    → {!! link_to_route('families.show', $descendant->name, [$descendant->id]) !!}
                    @if ($descendant->description)
                        <div class="description-container">
                            <div id="short-desc-desc-{{ $descendant->id }}">
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($descendant->description, 100) }}</small>
                                @if(strlen($descendant->description) > 100)
                                    <button type="button" class="btn btn-link p-0 toggle-desc" data-target="desc-{{ $descendant->id }}">{{ __('app.view_all') }}</button>
                                @endif
                            </div>
                            <div id="full-desc-desc-{{ $descendant->id }}" style="display:none;">
                                <small class="text-muted">{{ $descendant->description }}</small>
                                <button type="button" class="btn btn-link p-0 toggle-desc" data-target="desc-{{ $descendant->id }}">{{ __('app.show_less') }}</button>
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
@endsection

@section('ext_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-desc').forEach(function(button) {
        button.addEventListener('click', function() {
            var target = this.getAttribute('data-target');
            var shortDesc = document.getElementById('short-desc-' + target);
            var fullDesc = document.getElementById('full-desc-' + target);
            
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