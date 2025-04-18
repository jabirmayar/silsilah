<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ __('user.family') }}</h3></div>

    <table class="table">
        <tbody>
            <tr>
                <th class="col-sm-4">{{ __('app.family') }}</th>
                <td class="col-sm-8">
                    @can ('edit', $user)
                        @if (request('action') == 'set_family')
                        {{ Form::open(['route' => ['family-actions.set-family', $user->id]]) }}
                        
                        <div class="form-group">
                            <label>{{ __('app.main_family') }}</label>
                            <select name="family_id" class="form-control family-select" data-placeholder="{{ __('app.search_for_family') }}">
                                @if ($user->family)
                                    <option value="{{ $user->family_id }}" selected>{{ $user->family->name }}</option>
                                @endif
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>{{ __('app.sub_family') }}</label>
                            <select name="sub_family_id" class="form-control family-select" data-placeholder="{{ __('app.search_for_sub_family') }}">
                                @if ($user->subFamily)
                                    <option value="{{ $user->subFamily->id }}" selected>{{ $user->subFamily->name }}</option>
                                @endif
                            </select>
                            <small class="text-muted">{{ __('app.sub_family_helper_text') }}</small>
                        </div>
                        
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="create_new_family"> {{ __('app.create_new_family') }}
                            </label>
                        </div>
                        
                        <div id="new_family_form" style="display: none;">
                            <div class="form-group">
                                {{ Form::text('new_family_name', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.family_name')]) }}
                            </div>
                            
                            <div class="form-group">
                                {{ Form::textarea('new_family_description', null, ['class' => 'form-control input-sm', 'rows' => 2, 'placeholder' => __('app.family_description')]) }}
                            </div>
                            
                            <div class="form-group">
                                <label>{{ __('app.main_family') }}</label>
                                <select name="parent_family_id" class="form-control family-select" data-placeholder="{{ __('app.search_parent_family') }}">
                                    @if ($user->family && $user->family->parent)
                                        <option value="{{ $user->family->parent_id }}" selected>{{ $user->family->parent->name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="create_new_sub_family"> {{ __('app.create_new_sub_family') }}
                            </label>
                        </div>
                        
                        <div id="new_sub_family_form" style="display: none;">
                            <div class="form-group">
                                {{ Form::text('new_sub_family_name', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.sub_family_name')]) }}
                            </div>
                            
                            <div class="form-group">
                                {{ Form::textarea('new_sub_family_description', null, ['class' => 'form-control input-sm', 'rows' => 2, 'placeholder' => __('app.sub_family_description')]) }}
                            </div>
                            
                            <div class="form-group">
                                <label>{{ __('app.parent_of_sub_family') }}</label>
                                <select name="sub_family_parent_id" class="form-control family-select" data-placeholder="{{ __('app.search_parent_for_sub_family') }}">
                                    @if ($user->family)
                                        <option value="{{ $user->family_id }}" selected>{{ $user->family->name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary btn-sm', 'id' => 'set_family_button']) }}
                            {{ link_to_route('users.show', __('app.cancel'), [$user->id], ['class' => 'btn btn-default btn-sm']) }}
                        </div>
                        
                        {{ Form::close() }}
                        @else
                            <div class="family-hierarchy">
                                @if ($user->family)
                                    <div class="main-family">
                                        <strong>{{ __('app.main_family') }}:</strong> {{ $user->familyLink() }}
                                    </div>
                                    
                                    @if ($user->family->parent)
                                    <div class="parent-families">
                                        <small>{{ __('app.main_family') }}: 
                                        @php
                                            $parentFamily = $user->family->parent;
                                            $parentChain = [];
                                            while ($parentFamily) {
                                                $parentChain[] = link_to_route('families.show', $parentFamily->name, [$parentFamily->id]);
                                                $parentFamily = $parentFamily->parent;
                                            }
                                            echo implode(' → ', array_reverse($parentChain));
                                        @endphp
                                        </small>
                                    </div>
                                    @endif
                                @else
                                    <div class="main-family">
                                        <strong>{{ __('app.main_family') }}:</strong> {{ __('app.not_set') }}
                                    </div>
                                @endif
                                
                                @if ($user->subFamily)
                                    <div class="sub-family mt-2">
                                        <strong>{{ __('app.sub_family') }}:</strong> {{ link_to_route('families.show', $user->subFamily->name, [$user->subFamily->id]) }}
                                        
                                        @if ($user->subFamily->parent && $user->subFamily->parent->id != $user->family_id)
                                            <small>({{ __('app.parent') }}: {{ link_to_route('families.show', $user->subFamily->parent->name, [$user->subFamily->parent->id]) }})</small>
                                        @endif
                                    </div>
                                @else
                                    <div class="sub-family mt-2">
                                        <strong>{{ __('app.sub_family') }}:</strong> {{ __('app.not_set') }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="pull-right">
                                {{ link_to_route('users.show', __('app.change_family'), [$user->id, 'action' => 'set_family'], ['class' => 'btn btn-link btn-xs']) }}
                            </div>
                        @endif
                    @else
                        <div class="family-hierarchy">
                            @if ($user->family)
                                <div><strong>{{ __('app.main_family') }}:</strong> {{ $user->familyLink() }}</div>
                                
                                @if ($user->family->parent)
                                    <small>({{ __('app.parent') }}: {{ link_to_route('families.show', $user->family->parent->name, [$user->family->parent_id]) }})</small>
                                @endif
                            @else
                                <div><strong>{{ __('app.main_family') }}:</strong> {{ __('app.not_set') }}</div>
                            @endif
                            
                            @if ($user->subFamily)
                                <div class="mt-1"><strong>{{ __('app.sub_family') }}:</strong> {{ link_to_route('families.show', $user->subFamily->name, [$user->subFamily->id]) }}</div>
                            @else
                                <div class="mt-1"><strong>{{ __('app.sub_family') }}:</strong> {{ __('app.not_set') }}</div>
                            @endif
                        </div>
                    @endcan
                </td>
            </tr>            
            <tr>
                <th class="col-sm-4">{{ __('user.father') }}</th>
                <td class="col-sm-8">
                    @can ('edit', $user)
                        @if (request('action') == 'set_father')
                        {{ Form::open(['route' => ['family-actions.set-father', $user->id]]) }}
                        <select name="set_father_id" id="father-select" class="form-control person-search" data-placeholder="{{ __('app.select_from_existing_males') }}">
                            @if ($user->father_id)
                                <option value="{{ $user->father_id }}" selected>{{ $user->father->name }}</option>
                            @endif
                        </select>
                        <div class="input-group mt-2">
                            {{ Form::text('set_father', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.enter_new_name')]) }}
                            <span class="input-group-btn">
                                {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary btn-sm', 'id' => 'set_father_button']) }}
                                {{ link_to_route('users.show', __('app.cancel'), [$user->id], ['class' => 'btn btn-default btn-sm']) }}
                            </span>
                        </div>
                        {{ Form::close() }}
                        @else
                            {{ $user->fatherLink() }}
                            <div class="pull-right">
                                {{ link_to_route('users.show', __('user.set_father'), [$user->id, 'action' => 'set_father'], ['class' => 'btn btn-link btn-xs']) }}
                            </div>
                        @endif
                    @else
                        {{ $user->fatherLink() }}
                    @endcan
                </td>
            </tr>
            <tr>
                <th>{{ __('user.mother') }}</th>
                <td>
                    @can ('edit', $user)
                        @if (request('action') == 'set_mother')
                        {{ Form::open(['route' => ['family-actions.set-mother', $user->id]]) }}
                        <select name="set_mother_id" id="mother-select" class="form-control person-search" data-placeholder="{{ __('app.select_from_existing_females') }}" data-gender="female">
                            @if ($user->mother_id)
                                <option value="{{ $user->mother_id }}" selected>{{ $user->mother->name }}</option>
                            @endif
                        </select>
                        <div class="input-group mt-2">
                            {{ Form::text('set_mother', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.enter_new_name')]) }}
                            <span class="input-group-btn">
                                {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary btn-sm', 'id' => 'set_mother_button']) }}
                                {{ link_to_route('users.show', __('app.cancel'), [$user->id], ['class' => 'btn btn-default btn-sm']) }}
                            </span>
                        </div>
                        {{ Form::close() }}
                        @else
                            {{ $user->motherLink() }}
                            <div class="pull-right">
                                {{ link_to_route('users.show', __('user.set_mother'), [$user->id, 'action' => 'set_mother'], ['class' => 'btn btn-link btn-xs']) }}
                            </div>
                        @endif
                    @else
                        {{ $user->motherLink() }}
                    @endcan
                </td>
            </tr>
            <tr>
                <th class="col-sm-4">{{ __('user.parent') }}</th>
                <td class="col-sm-8">
                    @can ('edit', $user)
                    <div class="pull-right">
                        @unless (request('action') == 'set_parent')
                            {{ link_to_route('users.show', __('user.set_parent'), [$user->id, 'action' => 'set_parent'], ['class' => 'btn btn-link btn-xs']) }}
                        @endunless
                    </div>
                    @endcan
                    @if ($user->parent)
                    {{ $user->parent->husband->name }} & {{ $user->parent->wife->name }}
                    @endif
                    @can('edit', $user)
                        @if (request('action') == 'set_parent')
                            {{ Form::open(['route' => ['family-actions.set-parent', $user->id]]) }}
                            <select name="set_parent_id" class="form-control couple-search" data-placeholder="{{ __('app.select_from_existing_couples') }}">
                                @if ($user->parent_id)
                                    <option value="{{ $user->parent_id }}" selected>
                                        {{ $user->parent->husband->name }} & {{ $user->parent->wife->name }}
                                    </option>
                                @endif
                            </select>
                            {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary btn-sm mt-2', 'id' => 'set_parent_button']) }}
                            {{ link_to_route('users.show', __('app.cancel'), $user, ['class' => 'btn btn-default btn-sm mt-2']) }}
                            {{ Form::close() }}
                        @endif
                    @endcan
                </td>
            </tr>
            @if ($user->gender_id == 1)
            <tr>
                <th>{{ __('user.wife') }}</th>
                <td>
                    @can ('edit', $user)
                    <div class="pull-right">
                        @unless (request('action') == 'add_spouse')
                            {{ link_to_route('users.show', __('user.add_wife'), [$user->id, 'action' => 'add_spouse'], ['class' => 'btn btn-link btn-xs']) }}
                        @endunless
                    </div>
                    @endcan
                    @if ($user->wifes->isEmpty() == false)
                        <ul class="list-unstyled">
                            @foreach($user->wifes as $wife)
                            <li>{{ $wife->profileLink() }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @can('edit', $user)
                        @if (request('action') == 'add_spouse')
                        <div>
                            {{ Form::open(['route' => ['family-actions.add-wife', $user->id]]) }}
                            <select name="set_wife_id" class="form-control person-search" data-placeholder="{{ __('app.select_from_existing_females') }}" data-gender="female">
                            </select>
                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-md-7">
                                        {{ Form::text('set_wife', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.enter_new_name')]) }}
                                    </div>
                                    <div class="col-md-5">
                                        {{ Form::text('marriage_date', null, ['class' => 'form-control input-sm datepicker', 'placeholder' => __('couple.marriage_date')]) }}
                                    </div>
                                </div>
                            </div>
                            {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary btn-sm', 'id' => 'set_wife_button']) }}
                            {{ link_to_route('users.show', __('app.cancel'), $user, ['class' => 'btn btn-default btn-sm']) }}
                            {{ Form::close() }}
                        </div>
                        @endif
                    @endcan
                </td>
            </tr>
        @else
            <tr>
                <th>{{ __('user.husband') }}</th>
                <td>
                    @can ('edit', $user)
                    <div class="pull-right">
                        @unless (request('action') == 'add_spouse')
                            {{ link_to_route('users.show', __('user.add_husband'), [$user->id, 'action' => 'add_spouse'], ['class' => 'btn btn-link btn-xs']) }}
                        @endunless
                    </div>
                    @endcan
                    @if ($user->husbands->isEmpty() == false)
                        <ul class="list-unstyled">
                            @foreach($user->husbands as $husband)
                            <li>{{ $husband->profileLink() }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @can('edit', $user)
                        @if (request('action') == 'add_spouse')
                        <div>
                            {{ Form::open(['route' => ['family-actions.add-husband', $user->id]]) }}
                            <select name="set_husband_id" class="form-control person-search" data-placeholder="{{ __('app.select_from_existing_males') }}" data-gender="male">
                            </select>
                            <div class="form-group mt-2">
                                <div class="row">
                                    <div class="col-md-7">
                                        {{ Form::text('set_husband', null, ['class' => 'form-control input-sm', 'placeholder' => __('app.enter_new_name')]) }}
                                    </div>
                                    <div class="col-md-5">
                                        {{ Form::text('marriage_date', null, ['class' => 'form-control input-sm datepicker', 'placeholder' => __('couple.marriage_date')]) }}
                                    </div>
                                </div>
                            </div>
                            {{ Form::submit(__('app.update'), ['class' => 'btn btn-primary btn-sm', 'id' => 'set_husband_button']) }}
                            {{ link_to_route('users.show', __('app.cancel'), [$user->id], ['class' => 'btn btn-default btn-sm']) }}
                            {{ Form::close() }}
                        </div>
                        @endif
                    @endcan
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
