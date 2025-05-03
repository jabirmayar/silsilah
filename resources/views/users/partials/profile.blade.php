<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">{{ trans('user.profile') }}</h3></div>
    <div class="panel-body text-center">
        {{ userPhoto($user, ['style' => 'width:100%;max-width:300px']) }}
    </div>
    <table class="table">
        <tbody>
            <tr>
                <th class="col-sm-4">{{ trans('user.name') }}</th>
                <td class="col-sm-8">{{ $user->profileLink() }}</td>
            </tr>
            <tr>
                <th>{{ trans('user.nickname') }}</th>
                <td>{{ $user->nickname }}</td>
            </tr>
            <tr>
                <th>{{ trans('user.gender') }}</th>
                <td>{{ $user->gender }}</td>
            </tr>
            <tr>
                <th>{{ trans('user.dob') }}</th>
                <td>{{ $user->dob }}</td>
            </tr>
            <tr>
                <th>{{ trans('user.birth_order') }}</th>
                <td>{{ $user->birth_order }}</td>
            </tr>
            @if ($user->dod)
            <tr>
                <th>{{ trans('user.dod') }}</th>
                <td>{{ $user->dod }}</td>
            </tr>
            @endif
            <tr>
                <th>{{ trans('user.age') }}</th>
                <td>
                    @if ($user->age)
                        {!! $user->age_string !!}
                    @endif
                </td>
            </tr>
            @if (Auth::check() && is_system_admin(Auth::user()) || Auth::user()->id == $user->id)
             <tr>
                <th>{{ trans('app.status') }}</th>
                <td>
                    @if ($user->status === 1)
                        <span class="label label-success">{{ trans('app.active') }}</span>
                    @elseif ($user->status === 0)
                        <span class="label label-warning">{{ trans('app.inactive') }}</span>
                    @else
                        <span class="label label-default">{{ trans('app.unknown') }}</span>
                    @endif
                </td>
            </tr>
            @endif
            @if ($user->email)
            <tr>
                <th>{{ trans('user.email') }}</th>
                <td>{{ $user->email }}</td>
            </tr>
            @endif
            <tr>
                <th>{{ trans('user.phone') }}</th>
                <td>{{ $user->phone }}</td>
            </tr>
            <tr>
                <th>{{ trans('user.address') }}</th>
                <td>{!! nl2br($user->address) !!}</td>
            </tr>

            {{-- Admin Status Change Controls --}}
            @if (Auth::check() && is_system_admin(Auth::user()) && Auth::user()->id != $user->id)
            <tr>
                <th>{{ trans('app.change_status') }}</th>
                 <td>
                    @if ($user->status === 1)
                        <form method="POST" action="{{ route('users.update_status', $user->id) }}" style="display: inline-block;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="0">
                            <input type="hidden" name="origin" value="show">
                            <button type="submit" class="btn btn-warning btn-xs">
                                {{ trans('app.deactivate') }}
                            </button>
                        </form>
                    @elseif ($user->status === 0)
                        <form method="POST" action="{{ route('users.update_status', $user->id) }}" style="display: inline-block;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="1">
                            <input type="hidden" name="origin" value="show">
                            <button type="submit" class="btn btn-success btn-xs">
                                {{ trans('app.activate') }}
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @endif
            {{-- End Admin Status Change Controls --}}

        </tbody>
    </table>
</div>